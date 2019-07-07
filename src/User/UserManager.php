<?php

namespace Drupal\social_auth\User;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Transliteration\PhpTransliteration;
use Drupal\Core\Utility\Token;
use Drupal\social_api\User\UserManager as SocialApiUserManager;
use Drupal\social_auth\Event\UserFieldsEvent;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\UserEvent;
use Drupal\social_auth\SettingsTrait;
use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manages database related tasks.
 */
class UserManager extends SocialApiUserManager {

  use StringTranslationTrait;
  use SettingsTrait;

  /**
   * Used for access Drupal user field definitions.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Used for user picture directory and file transliteration.
   *
   * @var \Drupal\Core\Transliteration\PhpTransliteration
   */
  protected $transliteration;

  /**
   * Used to get the current UI language.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Used for token support in Drupal user picture directory.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Used for loading and creating Drupal user.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Used to display messages to user.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Used for logging errors.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Used for accessing Drupal configuration.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Used for access Drupal user field definitions.
   * @param \Drupal\Core\Transliteration\PhpTransliteration $transliteration
   *   Used for user picture directory and file transliteration.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Used to get current UI language.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Used for dispatching social auth events.
   * @param \Drupal\Core\Utility\Token $token
   *   Used for token support in Drupal user picture directory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              MessengerInterface $messenger,
                              LoggerChannelFactoryInterface $logger_factory,
                              ConfigFactoryInterface $config_factory,
                              EntityFieldManagerInterface $entity_field_manager,
                              PhpTransliteration $transliteration,
                              LanguageManagerInterface $language_manager,
                              EventDispatcherInterface $event_dispatcher,
                              Token $token) {

    parent::__construct('social_auth', $entity_type_manager, $messenger, $logger_factory);

    $this->configFactory = $config_factory;
    $this->entityFieldManager = $entity_field_manager;
    $this->transliteration = $transliteration;
    $this->languageManager = $language_manager;
    $this->eventDispatcher = $event_dispatcher;
    $this->token = $token;
  }

  /**
   * Creates a new user.
   *
   * @param \Drupal\social_auth\User\SocialAuthUserInterface $user
   *   The data of the user to be created.
   *
   * @return \Drupal\user\UserInterface|null
   *   The Drupal user if successful
   *   Null otherwise.
   */
  public function createNewUser(SocialAuthUserInterface $user) {
    // Download profile picture for the newly created user.
    if ($user->getPictureUrl()) {
      $this->setProfilePic($user);
    }

    $drupal_user = $this->createUser($user);

    if ($drupal_user) {
      // If the new user could be registered.
      $this->addUserRecord($drupal_user->id(),
                           $user->getProviderId(),
                           $user->getToken(),
                           $user->getAdditionalData());

      if ($this->saveUser($drupal_user)) {
        return $drupal_user;
      }
    }

    return NULL;
  }

  /**
   * Create a new user account.
   *
   * @param \Drupal\social_auth\User\SocialAuthUserInterface $user
   *   The data of the user to be created.
   *
   * @return \Drupal\user\Entity\User|false
   *   Drupal user account if user was created
   *   False otherwise
   */
  public function createUser(SocialAuthUserInterface $user) {

    $name = $user->getName();
    $email = $user->getEmail();

    // Make sure we have everything we need.
    if (!$name) {
      $this->loggerFactory
        ->get($this->getPluginId())
        ->error('Failed to create user. Name: @name', ['@name' => $name]);

      return FALSE;
    }

    // Check if site configuration allows new users to register.
    if ($this->isRegistrationDisabled()) {
      $this->loggerFactory
        ->get($this->getPluginId())
        ->warning('Failed to create user. User registration is disabled. Name: @name, email: @email.',
          ['@name' => $name, '@email' => $email]);

      $this->messenger->addError($this->t('User registration is disabled, please contact the administrator.'));

      return FALSE;
    }

    // Get the current UI language.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // Try to save the new user account.
    try {
      // Initializes the user fields.
      $fields = $this->getUserFields($user, $langcode);

      /** @var \Drupal\user\Entity\User $new_user */
      $new_user = $this->entityTypeManager
        ->getStorage('user')
        ->create($fields);

      $new_user->save();

      $this->loggerFactory
        ->get($this->getPluginId())
        ->notice('New user created. Username @username, UID: @uid', [
          '@username' => $new_user->getAccountName(),
          '@uid' => $new_user->id(),
        ]);

      // Dispatches SocialAuthEvents::USER_CREATED event.
      $event = new UserEvent($new_user, $this->getPluginId(), $user);
      $this->eventDispatcher->dispatch(SocialAuthEvents::USER_CREATED, $event);

      return $new_user;
    }
    catch (\Exception $ex) {
      $this->loggerFactory
        ->get($this->getPluginId())
        ->error('Could not create new user. Exception: @message', ['@message' => $ex->getMessage()]);
    }

    $this->messenger->addError($this->t('You could not be authenticated, please contact the administrator.'));

    return FALSE;
  }

  /**
   * Add user record in Social Auth Entity.
   *
   * @param int $user_id
   *   Drupal User ID.
   * @param string $provider_user_id
   *   Unique Social ID returned by social network.
   * @param string $token
   *   For making API calls.
   * @param string $user_data
   *   Additional user data collected.
   *
   * @return true
   *   if user record is added in social_auth entity table
   *   Else false.
   */
  public function addUserRecord($user_id, $provider_user_id, $token, $user_data) {
    // Make sure we have everything we need.
    if (!$user_id || !$this->pluginId || !$provider_user_id) {
      $this->loggerFactory
        ->get($this->getPluginId())
        ->error('Failed to add user record in Social Auth entity.
          User_id: @user_id, social_network_identifier: @social_network_identifier, provider_user_id : @provider_user_id ',
          [
            '@user_id' => $user_id,
            '@social_network_identifier' => $this->pluginId,
            '@provider_user_id ' => $provider_user_id,
          ]);

      $this->messenger->addError($this->t('You could not be authenticated, please contact the administrator.'));

      return FALSE;
    }
    else {
      // Add user record.
      $values = [
        'user_id' => $user_id,
        'plugin_id' => $this->pluginId,
        'provider_user_id' => $provider_user_id,
        'additional_data' => $user_data,
      ];

      try {
        $user_info = $this->entityTypeManager->getStorage('social_auth')->create($values);
        $user_info->setToken($token);

        // Save the entity.
        $user_info->save();
      }
      catch (\Exception $ex) {
        $this->loggerFactory
          ->get($this->getPluginId())
          ->error('Failed to add user record in Social Auth entity.
            Exception: @message', ['@message' => $ex->getMessage()]);

        $this->messenger->addError($this->t('You could not be authenticated, please contact the administrator.'));

        return FALSE;
      }

      return TRUE;
    }

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
    try {
      $users = $this->entityTypeManager
        ->getStorage('user')
        ->loadByProperties([$field => $value]);

      if (!empty($users)) {
        return current($users);
      }
    }
    catch (\Exception $ex) {
      $this->loggerFactory
        ->get($this->getPluginId())
        ->error('Failed to load user. Exception: @message', ['@message' => $ex->getMessage()]);
    }

    // If user was not found, return FALSE.
    return FALSE;
  }

  /**
   * Saves the Drupal user entity.
   *
   * @return bool
   *   True if picture was successfully set.
   *   False otherwise.
   */
  protected function saveUser(UserInterface $drupal_user) {
    try {
      $drupal_user->save();

      return TRUE;
    }
    catch (EntityStorageException $ex) {
      $this->loggerFactory
        ->get($this->getPluginId())
        ->error(
          'Failed to save user. Exception: @message',
            ['@message' => $ex->getMessage()]
        );

      return FALSE;
    }
  }

  /**
   * Downloads and sets user profile picture.
   *
   * @param \Drupal\social_auth\User\SocialAuthUserInterface $user
   *   The Social Auth User object.
   *
   * @return bool
   *   True if picture was successfully set.
   *   False otherwise.
   */
  protected function setProfilePic(SocialAuthUserInterface $user) {
    $picture_url = $user->getPictureUrl();
    $id = $user->getProviderId();

    // Tries to download the profile picture and add it to Social Auth User.
    if ($this->userPictureEnabled()) {
      $file = $this->downloadProfilePic($picture_url, $id);
      if ($file) {
        $user->setPicture($file->id());
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
   *
   * @return \Drupal\file\FileInterface|false
   *   FileInterface object if file was successfully downloaded
   *   False otherwise
   */
  protected function downloadProfilePic($picture_url, $id) {
    // Make sure that we have everything we need.
    if (!$picture_url || !$id) {
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
        ->get($this->getPluginId())
        ->error('Could not save @plugin_id\'s provider profile picture. Directory is not writable: @directory', [
          '@directory' => $directory,
          '@provider' => $this->getPluginId(),
        ]);

      return FALSE;
    }

    // Generate filename and transliterate.
    $filename = $this->transliteration->transliterate($this->getPluginId() . '_' . $id, 'en', '_', 50) . '.jpg';

    $destination = $directory . DIRECTORY_SEPARATOR . $filename;

    // Download the picture to local filesystem.
    if (!$file = $this->systemRetrieveFile($picture_url, $destination, TRUE, 1)) {
      $this->loggerFactory
        ->get($this->getPluginId())
        ->error('Could not download @plugin_id\'s provider profile picture from url: @url', [
          '@url' => $picture_url,
          '@plugin_id' => $this->getPluginId(),
        ]);

      return FALSE;
    }

    return $file;
  }

  /**
   * Ensures that Drupal usernames will be unique.
   *
   * Drupal usernames will be generated so that the user's full name on provider
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
    $name = mb_substr($name, 0, $max_length);
    $name = str_replace(' ', '', $name);
    $name = strtolower($name);

    // Add a trailing number if needed to make username unique.
    $base = $name;
    $i = 1;
    $candidate = $base;
    while ($this->loadUserByProperty('name', $candidate)) {
      // Calculate max length for $base and truncate if needed.
      $max_length_base = $max_length - strlen((string) $i) - 1;
      $base = mb_substr($base, 0, $max_length_base);
      $candidate = $base . $i;
      $i++;
    }

    // Trim leading and trailing whitespace.
    $candidate = trim($candidate);

    return $candidate;
  }

  /**
   * Returns an array of fields to initialize the creation of the user.
   *
   * @param \Drupal\social_auth\User\SocialAuthUserInterface $user
   *   The data of the user to be created.
   * @param string $langcode
   *   The current UI language.
   *
   * @return array
   *   Fields to initialize for the user creation.
   */
  protected function getUserFields(SocialAuthUserInterface $user, $langcode) {
    $fields = [
      'name' => $this->generateUniqueUsername($user->getName()),
      'mail' => $user->getEmail(),
      'init' => $user->getEmail(),
      'pass' => $this->userPassword(32),
      'status' => $this->getNewUserStatus(),
      'langcode' => $langcode,
      'preferred_langcode' => $langcode,
      'preferred_admin_langcode' => $langcode,
      'user_picture' => $user->getPicture(),
    ];

    // Dispatches SocialAuthEvents::USER_FIELDS, so that other modules can
    // update this array before an user is saved.
    $event = new UserFieldsEvent($fields, $this->getPluginId(), $user);
    $this->eventDispatcher->dispatch(SocialAuthEvents::USER_FIELDS, $event);
    $fields = $event->getUserFields();

    return $fields;
  }

  /**
   * Returns whether this site supports the default user picture feature.
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

}
