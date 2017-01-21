<?php

namespace Drupal\social_auth;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Transliteration\PhpTransliteration;
use Drupal\Core\Utility\Token;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\SocialAuthUserEvent;
use Drupal\social_auth\Event\SocialAuthUserFieldsEvent;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Contains all logic that is related to Drupal user management.
 */
class SocialAuthUserManager {
  use StringTranslationTrait;

  protected $configFactory;
  protected $loggerFactory;
  protected $eventDispatcher;
  protected $entityTypeManager;
  protected $entityFieldManager;
  protected $token;
  protected $stringTranslation;
  protected $transliteration;
  protected $languageManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Used for accessing Drupal configuration.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Used for logging errors.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Used for dispatching social auth events.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Used for loading and creating Drupal user objects.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Used for access Drupal user field definitions.
   * @param \Drupal\Core\Utility\Token $token
   *   Used for token support in Drupal user picture directory.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   Used for translating strings in UI messages.
   * @param \Drupal\Core\Transliteration\PhpTransliteration $transliteration
   *   Used for user picture directory and file transliteration.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory, EventDispatcherInterface $event_dispatcher, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, Token $token, TranslationInterface $string_translation, PhpTransliteration $transliteration, LanguageManagerInterface $language_manager) {
    $this->configFactory      = $config_factory;
    $this->loggerFactory      = $logger_factory;
    $this->eventDispatcher    = $event_dispatcher;
    $this->entityTypeManager  = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->token              = $token;
    $this->stringTranslation  = $string_translation;
    $this->transliteration    = $transliteration;
    $this->languageManager    = $language_manager;
  }

  /**
   * Loads existing Drupal user object by given property and value.
   *
   * Note that first matching user is returned. Email address and account name
   * are unique so there can be only zero or one matching user when
   * loading users by these properties.
   *
   * @param string $field
   *   User entity field to search from.
   * @param string $value
   *   Value to search for.
   *
   * @return \Drupal\user\Entity\User|false
   *   Drupal user account if found
   *   False otherwise
   */
  public function loadUserByProperty($field, $value) {
    $users = $this->entityTypeManager
      ->getStorage('user')
      ->loadByProperties(array($field => $value));

    if (!empty($users)) {
      return current($users);
    }

    // If user was not found, return FALSE.
    return FALSE;
  }

  /**
   * Create a new user account.
   *
   * @param string $name
   *   User's name on Provider.
   * @param string $email
   *   User's email address.
   * @param string $plugin_id
   *   Plugin ID creating the user.
   *
   * @return \Drupal\user\Entity\User|false
   *   Drupal user account if user was created
   *   False otherwise
   */
  public function createUser($name, $email, $plugin_id = 'social_auth') {
    // Make sure we have everything we need.
    if (!$name || !$email) {
      $this->loggerFactory
        ->get($plugin_id)
        ->error('Failed to create user. Name: @name, email: @email', array('@name' => $name, '@email' => $email));
      return FALSE;
    }

    // Check if site configuration allows new users to register.
    if ($this->registrationBlocked()) {
      $this->loggerFactory
        ->get($plugin_id)
        ->warning('Failed to create user. User registration is disabled in Drupal account settings. Name: @name, email: @email.', array('@name' => $name, '@email' => $email));

      $this->drupalSetMessage($this->t('Only existing users can log in with Facebook. Contact system administrator.'), 'error');
      return FALSE;
    }

    // Get the current UI language.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // Initializes the user fields.
    $fields = $this->getUserFields($name, $email, $langcode, $plugin_id);

    // Create new user account.
    /** @var \Drupal\user\Entity\User $new_user */
    $new_user = $this->entityTypeManager
      ->getStorage('user')
      ->create($fields);

    // Validate the new user.
    $violations = $new_user->validate();
    if (count($violations) > 0) {
      $msg = $violations[0]->getMessage();
      $this->drupalSetMessage($this->t('Creation of user account failed: @message', array('@message' => $msg)), 'error');
      $this->loggerFactory
        ->get($plugin_id)
        ->error('Could not create new user: @message', array('@message' => $msg));
      return FALSE;
    }

    // Try to save the new user account.
    try {
      $new_user->save();

      $this->loggerFactory
        ->get($plugin_id)
        ->notice('New user created. Username @username, UID: @uid', [
          '@username' => $new_user->getAccountName(),
          '@uid' => $new_user->id(),
        ]);

      // Dipatches SocialAuthEvents::USER_CREATED event.
      $event = new SocialAuthUserEvent($new_user, $plugin_id);
      $this->eventDispatcher->dispatch(SocialAuthEvents::USER_CREATED, $event);

      return $new_user;
    }
    catch (EntityStorageException $ex) {
      $this->loggerFactory
        ->get($plugin_id)
        ->error('Could not create new user. Exception: @message', ['@message' => $ex->getMessage()]);
    }

    return FALSE;
  }

  /**
   * Logs the user in.
   *
   * @param \Drupal\user\Entity\User $drupal_user
   *   User object.
   *
   * @return bool
   *   True if login was successful
   *   False if the login was blocked
   */
  public function loginUser(User $drupal_user, $plugin_id = 'social_auth') {
    // Check that the account is active and log the user in.
    if ($drupal_user->isActive()) {
      $this->userLoginFinalize($drupal_user);

      // Dipatches SocialAuthEvents::USER_LOGIN event.
      $event = new SocialAuthUserEvent($drupal_user, $plugin_id);
      $this->eventDispatcher->dispatch(SocialAuthEvents::USER_LOGIN, $event);

      return TRUE;
    }

    $this->loggerFactory
      ->get($plugin_id)
      ->warning('Login for user @user prevented. Account is blocked.', array('@user' => $drupal_user->getAccountName()));

    return FALSE;
  }

  /**
   * Checks if user registration is blocked in Drupal account settings.
   *
   * @return bool
   *   True if registration is blocked
   *   False if registration is not blocked
   */
  protected function registrationBlocked() {
    // Check if Drupal account registration settings is Administrators only.
    if ($this->configFactory
      ->get('user.settings')
      ->get('register') == 'admin_only') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Ensures that Drupal usernames will be unique.
   *
   * Drupal usernames will be generated so that the user's full name on Provider
   * will become user's Drupal username. This method will check if the username
   * is already used and appends a number until it finds the first available
   * username.
   *
   * @param string $name
   *   User's full name on provider.
   *
   * @return string
   *   Unique drupal username.
   */
  protected function generateUniqueUsername($name) {
    $max_length = 60;
    $name = Unicode::substr($name, 0, $max_length);

    // Add a trailing number if needed to make username unique.
    $base = $name;
    $i = 1;
    $candidate = $base;
    while ($this->loadUserByProperty('name', $candidate)) {
      $i++;
      // Calculate max length for $base and truncate if needed.
      $max_length_base = $max_length - strlen((string) $i) - 1;
      $base = Unicode::substr($base, 0, $max_length_base);
      $candidate = $base . ' ' . $i;
    }

    // Trim leading and trailing whitespace.
    $candidate = trim($candidate);

    return $candidate;
  }

  /**
   * Returns the status for new users.
   *
   * @return int
   *   Value 0 means that new accounts remain blocked and require approval.
   *   Value 1 means that visitors can register new accounts without approval.
   */
  protected function getNewUserStatus() {
    if ($this->configFactory
      ->get('user.settings')
      ->get('register') == 'visitors') {
      return 1;
    }

    return 0;
  }

  /**
   * Downloads and sets user profile picture.
   *
   * @param User $drupal_user
   *   User object to update the profile picture for.
   * @param string $picture_url
   *   Absolute URL where the picture will be downloaded from.
   * @param string $id
   *   User's ID.
   * @param string $plugin_id
   *   Social Auth plugin id.
   *
   * @return bool
   *   True if picture was successfully set.
   *   False otherwise.
   */
  public function setProfilePic(User $drupal_user, $picture_url, $id, $plugin_id = 'social_auth') {
    // Try to download the profile picture and add it to user fields.
    if ($this->userPictureEnabled()) {
      if ($file = $this->downloadProfilePic($picture_url, $id, $plugin_id)) {
        $drupal_user->set('user_picture', $file->id());
        $drupal_user->save();
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Downloads the profile picture to Drupal filesystem.
   *
   * @param string $picture_url
   *   Absolute URL where to download the profile picture.
   * @param string $id
   *   Social network ID of the user.
   * @param string $plugin_id
   *   Social Auth plugin id.
   *
   * @return \Drupal\file\FileInterface|false
   *   FileInterface object if file was succesfully downloaded
   *   False otherwise
   */
  protected function downloadProfilePic($picture_url, $id, $plugin_id = 'social_auth') {
    // Make sure that we have everything we need.
    if (!$picture_url || !$id || !$plugin_id) {
      return FALSE;
    }

    // Determine target directory.
    $scheme = $this->configFactory->get('system.file')->get('default_scheme');
    $file_directory = $this->getPictureDirectory();

    if (!$file_directory) {
      return FALSE;
    }
    $directory = $scheme . '://' . $file_directory;

    // Replace tokens.
    $directory = $this->token->replace($directory);

    // Transliterate directory name.
    $directory = $this->transliteration->transliterate($directory, 'en', '_', 50);

    if (!$this->filePrepareDirectory($directory, 1)) {
      $this->loggerFactory
        ->get($plugin_id)
        ->error('Could not save @plugin_id\'s provider profile picture. Directory is not writable: @directory', [
          '@directory' => $directory,
          '@provider' => $plugin_id,
        ]);
      return FALSE;
    }

    // Generate filename and transliterate.
    $filename = $this->transliteration->transliterate($plugin_id . '_' . $id . '.jpg', 'en', '_', 50);

    $destination = $directory . DIRECTORY_SEPARATOR . $filename;

    // Download the picture to local filesystem.
    if (!$file = $this->systemRetrieveFile($picture_url, $destination, TRUE, 1)) {
      $this->loggerFactory
        ->get($plugin_id)
        ->error('Could not download @plugin_id\'s provider profile picture from url: @url', [
          '@url' => $picture_url,
          '@plugin_id' => $plugin_id,
        ]);

      return FALSE;
    }

    return $file;
  }

  /**
   * Returns whether this site supports the default user picture feature.
   *
   * We use this method instead of the procedural user_pictures_enabled()
   * so that we can unit test our own methods.
   *
   * @return bool
   *   True if user pictures are enabled
   *   False otherwise
   */
  protected function userPictureEnabled() {
    $field_definitions = $this->entityFieldManager->getFieldDefinitions('user', 'user');
    return isset($field_definitions['user_picture']);
  }

  /**
   * Returns picture directory if site supports the user picture feature.
   *
   * @return string|bool
   *   Directory for user pictures if site supports user picture feature.
   *   False otherwise.
   */
  protected function getPictureDirectory() {
    $field_definitions = $this->entityFieldManager->getFieldDefinitions('user', 'user');
    if (isset($field_definitions['user_picture'])) {
      return $field_definitions['user_picture']->getSetting('file_directory');
    }
    return FALSE;
  }

  /**
   * Wrapper for file_prepare_directory.
   *
   * We need to wrap the legacy procedural Drupal API functions so that we are
   * not using them directly in our own methods. This way we can unit test our
   * own methods.
   *
   * @see file_prepare_directory
   */
  protected function filePrepareDirectory(&$directory, $options) {
    return file_prepare_directory($directory, $options);
  }

  /**
   * Wrapper for system_retrieve_file.
   *
   * We need to wrap the legacy procedural Drupal API functions so that we are
   * not using them directly in our own methods. This way we can unit test our
   * own methods.
   *
   * @see system_retrieve_file
   */
  protected function systemRetrieveFile($url, $destination, $managed, $replace) {
    return system_retrieve_file($url, $destination, $managed, $replace);
  }

  /**
   * Wrapper for user_password.
   *
   * We need to wrap the legacy procedural Drupal API functions so that we are
   * not using them directly in our own methods. This way we can unit test our
   * own methods.
   *
   * @param int $length
   *   Length of the password.
   *
   * @return string
   *   The password.
   *
   * @see user_password
   */
  protected function userPassword($length) {
    return user_password($length);
  }

  /**
   * Wrapper for user_login_finalize.
   *
   * We need to wrap the legacy procedural Drupal API functions so that we are
   * not using them directly in our own methods. This way we can unit test our
   * own methods.
   *
   * @see user_password
   */
  protected function userLoginFinalize(UserInterface $account) {
    user_login_finalize($account);
  }

  /**
   * Returns an array of fields to initialize the creation of the user.
   *
   * @param string $name
   *   User's name on Provider.
   * @param string $email
   *   User's email address.
   * @param string $langcode
   *   The current UI language.
   * @param string $plugin_id
   *   The plugin id.
   *
   * @return array
   *   Fields to initialize for the user creation.
   */
  protected function getUserFields($name, $email, $langcode, $plugin_id = 'social_auth') {
    // - Password can be very long since the user doesn't see this.
    $fields = [
      'name' => $this->generateUniqueUsername($name),
      'mail' => $email,
      'init' => $email,
      'pass' => $this->userPassword(32),
      'status' => $this->getNewUserStatus(),
      'langcode' => $langcode,
      'preferred_langcode' => $langcode,
      'preferred_admin_langcode' => $langcode,
    ];

    // Dispatches SocialAuthEvents::USER_FIELDS, so that other modules can
    // update this array before an user is saved.
    $event = new SocialAuthUserFieldsEvent($fields, $plugin_id);
    $this->eventDispatcher->dispatch(SocialAuthEvents::USER_FIELDS, $event);
    $fields = $event->getUserFields();

    return $fields;
  }

}
