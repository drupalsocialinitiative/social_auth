<?php

namespace Drupal\social_auth\User;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\social_api\User\UserAuthenticator as SocialApiUserAuthenticator;
use Drupal\social_auth\Event\BeforeRedirectEvent;
use Drupal\social_auth\Event\FailedAuthenticationEvent;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\UserEvent;
use Drupal\social_auth\SettingsTrait;
use Drupal\social_auth\SocialAuthDataHandler;
use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Manages Drupal authentication tasks for Social Auth.
 */
class UserAuthenticator extends SocialApiUserAuthenticator {

  use SettingsTrait;
  use StringTranslationTrait;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The Social Auth user manager.
   *
   * @var \Drupal\social_auth\User\UserManager
   */
  protected $userManager;

  /**
   * The redirection response to be returned.
   *
   * @var \Symfony\Component\HttpFoundation\RedirectResponse
   */
  protected $response;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Used to get current active user.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Used to display messages to user.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Used for logging errors.
   * @param \Drupal\social_auth\User\UserManager $user_manager
   *   The Social API user manager.
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   Used to interact with session.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Used for accessing Drupal configuration.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   Used to check if route path exists.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Used for dispatching social auth events.
   */
  public function __construct(AccountProxyInterface $current_user,
                              MessengerInterface $messenger,
                              LoggerChannelFactoryInterface $logger_factory,
                              UserManager $user_manager,
                              SocialAuthDataHandler $data_handler,
                              ConfigFactoryInterface $config_factory,
                              RouteProviderInterface $route_provider,
                              EventDispatcherInterface $event_dispatcher) {

    parent::__construct($current_user, $messenger, $logger_factory, $user_manager, $data_handler);

    $this->configFactory = $config_factory;
    $this->routeProvider = $route_provider;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * Sets the destination parameter path for redirection after login.
   *
   * @param string $destination
   *   The path to redirect to.
   */
  public function setDestination($destination) {
    $this->dataHandler->set('login_destination', $destination);
  }

  /**
   * Creates and/or authenticates an user.
   *
   * @param string $name
   *   The user's name.
   * @param string $email
   *   The user's email address.
   * @param string $provider_user_id
   *   The unique id returned by the user.
   * @param string $token
   *   The access token for making additional API calls.
   * @param string|bool $picture_url
   *   The user's picture.
   * @param string $data
   *   The additional user_data to be stored in database.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response.
   */
  public function authenticateUser($name, $email, $provider_user_id, $token, $picture_url = FALSE, $data = '') {

    // Checks for record in Social Auth entity.
    $user_id = $this->userManager->getDrupalUserId($provider_user_id);

    // If user is already authenticated.
    if ($this->currentUser->isAuthenticated()) {

      // If no record for provider exists.
      if ($user_id === FALSE) {
        $this->associateNewProvider($provider_user_id, $token, $data);

        return $this->response;
      }
      // User is authenticated and provider is already associated.
      else {
        return $this->getPostLoginRedirection();
      }
    }

    // If user previously authorized the provider, load user through provider.
    if ($user_id) {
      $this->authenticateWithProvider($user_id);

      return $this->response;
    }

    // Try to authenticate user using email address.
    if ($email) {
      // If authentication with email was successful.
      if ($this->authenticateWithEmail($email, $provider_user_id, $token, $data)) {
        return $this->response;
      }
    }

    $user = new SocialAuthUser($name, $email, $provider_user_id, $token, $picture_url, $data);

    // At this point, create a new user.
    $drupal_user = $this->userManager->createNewUser($user);

    $this->authenticateNewUser($drupal_user);

    return $this->response;
  }

  /**
   * Associates an existing user with a new provider.
   *
   * @param string $provider_user_id
   *   The unique id returned by the user.
   * @param string $token
   *   The access token for making additional API calls.
   * @param string $data
   *   The additional user_data to be stored in database.
   */
  public function associateNewProvider($provider_user_id, $token, $data) {
    if ($this->userManager->addUserRecord($this->currentUser->id(), $provider_user_id, $token, $data)) {
      $this->response = $this->getPostLoginRedirection();

      return;
    }

    $this->messenger->addError($this->t('New provider could not be associated.'));
    $this->response = $this->getLoginFormRedirection();
  }

  /**
   * Authenticates user using provider.
   *
   * @param int $user_id
   *   The Drupal user id.
   *
   * @return bool
   *   True is user provider could be associated.
   *   False otherwise.
   */
  public function authenticateWithProvider($user_id) {
    try {
      // Load the user by their Drupal user id.
      $drupal_user = $this->userManager->loadUserByProperty('uid', $user_id);

      if ($drupal_user) {
        // Authenticates and redirect existing user.
        $this->authenticateExistingUser($drupal_user);

        return TRUE;
      }

      return FALSE;
    }
    catch (\Exception $ex) {
      $this->loggerFactory
        ->get($this->getPluginId())
        ->error('Failed to authenticate user. Exception: @message', ['@message' => $ex->getMessage()]);

      return FALSE;
    }
  }

  /**
   * Authenticates user by email address.
   *
   * @param string $email
   *   The user's email address.
   * @param string $provider_user_id
   *   The unique id returned by the user.
   * @param string $token
   *   The access token for making additional API calls.
   * @param string $data
   *   The additional user_data to be stored in database.
   *
   * @return bool
   *   True if user could be authenticated with email.
   *   False otherwise.
   */
  public function authenticateWithEmail($email, $provider_user_id, $token, $data) {
    try {
      // Load user by email.
      $drupal_user = $this->userManager->loadUserByProperty('mail', $email);

      // Check if user with same email account exists.
      if ($drupal_user) {
        // Add record for the same user.
        $this->userManager->addUserRecord($drupal_user->id(), $provider_user_id, $token, $data);

        // Authenticates and redirect the user.
        $this->authenticateExistingUser($drupal_user);

        return TRUE;
      }
    }
    catch (\Exception $ex) {
      $this->loggerFactory
        ->get($this->getPluginId())
        ->error('Failed to authenticate user. Exception: @message', ['@message' => $ex->getMessage()]);
    }

    return FALSE;
  }

  /**
   * Authenticates and redirects existing users in authentication process.
   *
   * @param \Drupal\user\UserInterface $drupal_user
   *   User object to authenticate.
   */
  public function authenticateExistingUser(UserInterface $drupal_user) {
    // If Admin (user 1) can not authenticate.
    if ($this->isAdminDisabled($drupal_user)) {
      $this->nullifySessionKeys();
      $this->messenger->addError($this->t('Authentication for Admin (user 1) is disabled.'));

      $this->response = $this->getLoginFormRedirection();

      return;
    }

    // If user can not login because of their role.
    $disabled_role = $this->isUserRoleDisabled($drupal_user);
    if ($disabled_role) {
      $this->messenger->addError($this->t("Authentication for '@role' role is disabled.", ['@role' => $disabled_role]));

      $this->response = $this->getLoginFormRedirection();

      return;
    }

    // If user could be logged in.
    if ($this->loginUser($drupal_user)) {
      $this->response = $this->getPostLoginRedirection();
    }
    else {
      $this->nullifySessionKeys();
      $this->messenger->addError($this->t('Your account has not been approved yet or might have been canceled, please contact the administrator.'));

      $this->response = $this->getLoginFormRedirection();
    }
  }

  /**
   * Authenticates and redirects new users in authentication process.
   *
   * @param \Drupal\user\UserInterface|null $drupal_user
   *   User object to login.
   */
  public function authenticateNewUser(UserInterface $drupal_user = NULL) {

    // If it's a valid Drupal user.
    if ($drupal_user) {

      // If the account needs admin approval.
      if ($this->isApprovalRequired()) {
        $this->messenger->addWarning($this->t("Your account was created, but it needs administrator's approval."));
        $this->nullifySessionKeys();

        $this->response = $this->getLoginFormRedirection();

        return;
      }

      // If the new user could be logged in.
      if ($this->loginUser($drupal_user)) {
        // User form redirection or false if option is not enabled.
        $redirect = $this->redirectToUserForm($drupal_user);

        if ($redirect) {
          $this->response = $redirect;

          return;
        }

        $this->response = $this->getPostLoginRedirection();

        return;
      }
    }

    if (!$this->isRegistrationDisabled()) {
      $this->messenger->addError($this->t('You could not be authenticated. Contact site administrator.'));
    }

    $this->nullifySessionKeys();

    $this->response = $this->getLoginFormRedirection();
  }

  /**
   * Logs the user in.
   *
   * @param \Drupal\user\UserInterface $drupal_user
   *   User object.
   *
   * @return bool
   *   True if login was successful
   *   False if the login was blocked
   */
  public function loginUser(UserInterface $drupal_user) {
    // Check that the account is active and log the user in.
    if ($drupal_user->isActive()) {
      $this->userLoginFinalize($drupal_user);

      // Dispatches SocialAuthEvents::USER_LOGIN event.
      $event = new UserEvent($drupal_user, $this->getPluginId());
      $this->eventDispatcher->dispatch(SocialAuthEvents::USER_LOGIN, $event);

      return TRUE;
    }

    $this->loggerFactory
      ->get($this->getPluginId())
      ->warning('Login for user @user prevented. Account is blocked.', ['@user' => $drupal_user->getAccountName()]);

    return FALSE;
  }

  /**
   * Checks if provider is already associated to the Drupal user.
   *
   * @param string $provider_user_id
   *   User's id on provider.
   *
   * @return int|false
   *   The Drupal user id if it exists.
   *   False otherwise.
   */
  public function checkProviderIsAssociated($provider_user_id) {
    return $this->userManager->getDrupalUserId($provider_user_id);
  }

  /**
   * Returns redirection to user login form.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirection response.
   */
  protected function getLoginFormRedirection() {
    return new RedirectResponse(Url::fromRoute('user.login')->toString());
  }

  /**
   * Wrapper for user_login_finalize.
   *
   * We need to wrap the legacy procedural Drupal API functions so that we are
   * not using them directly in our own methods. This way we can unit test our
   * own methods.
   *
   * @param \Drupal\User\UserInterface $account
   *   The Drupal user.
   *
   * @see user_password
   */
  protected function userLoginFinalize(UserInterface $account) {
    user_login_finalize($account);
  }

  /**
   * Dispatch an event when authentication in provider fails.
   *
   * @param string|null $error
   *   The error string/code from provider.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Return redirect response.
   */
  public function dispatchAuthenticationError($error = NULL) {
    $event = new FailedAuthenticationEvent($this->dataHandler, $this->getPluginId(), $error ?? NULL);
    $this->eventDispatcher->dispatch(SocialAuthEvents::FAILED_AUTH, $event);

    if ($event->hasResponse()) {
      return $event->getResponse();
    }

    return NULL;
  }

  /**
   * Dispatch an event before user is redirected to the provider.
   *
   * @param string|null $destination
   *   The destination url.
   */
  public function dispatchBeforeRedirect($destination = NULL) {
    $event = new BeforeRedirectEvent($this->dataHandler, $this->getPluginId(), $destination);
    $this->eventDispatcher->dispatch(SocialAuthEvents::BEFORE_REDIRECT, $event);
  }

}
