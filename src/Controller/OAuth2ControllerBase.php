<?php

namespace Drupal\social_auth\Controller;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth\AuthManager\OAuth2ManagerInterface;
use Drupal\social_auth\SocialAuthDataHandler;
use Drupal\social_auth\User\UserAuthenticator;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handle responses for Social Auth implementer controllers.
 */
class OAuth2ControllerBase extends ControllerBase {

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The network plugin manager.
   *
   * @var \Drupal\social_api\Plugin\NetworkManager
   */
  protected $networkManager;

  /**
   * The Social Auth user authenticator..
   *
   * @var \Drupal\social_auth\User\UserAuthenticator
   */
  protected $userAuthenticator;

  /**
   * The provider authentication manager.
   *
   * @var \Drupal\social_auth\AuthManager\OAuth2ManagerInterface
   */
  protected $providerManager;

  /**
   * Used to access GET parameters.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The Social Auth data handler.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  protected $dataHandler;

  /**
   * The implement plugin id.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The module name.
   *
   * @var string
   */
  protected $module;

  /**
   * SocialAuthControllerBase constructor.
   *
   * @param string $module
   *   The module name.
   * @param string $plugin_id
   *   The plugin id.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   Used to get an instance of social_auth_google network plugin.
   * @param \Drupal\social_auth\User\UserAuthenticator $user_authenticator
   *   Used to manage user authentication/registration.
   * @param \Drupal\social_auth\AuthManager\OAuth2ManagerInterface $provider_manager
   *   Used to manage authentication methods.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Used to access GET parameters.
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   The Social Auth data handler.
   */
  public function __construct($module,
                              $plugin_id,
                              MessengerInterface $messenger,
                              NetworkManager $network_manager,
                              UserAuthenticator $user_authenticator,
                              OAuth2ManagerInterface $provider_manager,
                              RequestStack $request,
                              SocialAuthDataHandler $data_handler) {

    $this->module = $module;
    $this->pluginId = $plugin_id;
    $this->messenger = $messenger;
    $this->networkManager = $network_manager;
    $this->userAuthenticator = $user_authenticator;
    $this->providerManager = $provider_manager;
    $this->request = $request;
    $this->dataHandler = $data_handler;

    // Sets the plugin id in user authenticator.
    $this->userAuthenticator->setPluginId($plugin_id);

    // Sets the session prefix.
    $this->dataHandler->setSessionPrefix($plugin_id);

    // Sets the session keys to nullify if user could not logged in.
    $this->userAuthenticator->setSessionKeysToNullify(['access_token', 'oauth2state']);
  }

  /**
   * Response for implementer authentication url.
   *
   * Redirects the user to Provider for authentication.
   */
  public function redirectToProvider() {

    try {
      /* @var \League\OAuth2\Client\Provider\AbstractProvider|false $client */
      $client = $this->networkManager->createInstance($this->pluginId)->getSdk();

      // If provider client could not be obtained.
      if (!$client) {
        $this->messenger->addError($this->t('%module not configured properly. Contact site administrator.', ['%module' => $this->module]));
        return $this->redirect('user.login');
      }

      // If destination parameter is set, save it.
      $destination = $this->request->getCurrentRequest()->get('destination');

      if ($destination) {
        $this->userAuthenticator->setDestination($destination);
      }

      // Provider service was returned, inject it to $providerManager.
      $this->providerManager->setClient($client);

      // Generates the URL where the user will be redirected for authentication.
      $auth_url = $this->providerManager->getAuthorizationUrl();

      $state = $this->providerManager->getState();
      $this->dataHandler->set('oauth2state', $state);

      // Forces session to be saved before redirection.
      $this->dataHandler->save();

      $response = new TrustedRedirectResponse($auth_url);

      $response->send();

      return $response;
    }
    catch (PluginException $exception) {
      $this->messenger->addError($this->t('There has been an error when creating plugin.'));

      return $this->redirect('user.login');
    }
  }

  /**
   * Process implementer callback path.
   *
   * @return \League\OAuth2\Client\Provider\GenericResourceOwner|null
   *   The user info if successful.
   *   Null otherwise.
   */
  public function processCallback() {
    try {
      /* @var \League\OAuth2\Client\Provider\AbstractProvider|false $client */
      $client = $this->networkManager->createInstance($this->pluginId)->getSdk();

      // If provider client could not be obtained.
      if (!$client) {
        $this->messenger->addError($this->t('%module not configured properly. Contact site administrator.', ['%module' => $this->module]));

        return NULL;
      }

      $state = $this->dataHandler->get('oauth2state');

      // Retrieves $_GET['state'].
      $retrievedState = $this->request->getCurrentRequest()->query->get('state');

      if (empty($retrievedState) || ($retrievedState !== $state)) {
        $this->userAuthenticator->nullifySessionKeys();
        $this->messenger->addError($this->t('Login failed. Invalid OAuth2 state.'));

        return NULL;
      }

      $this->providerManager->setClient($client)->authenticate();

      // Saves access token to session.
      $this->dataHandler->set('access_token', $this->providerManager->getAccessToken());

      // Gets user's info from provider.
      if (!$profile = $this->providerManager->getUserInfo()) {
        $this->messenger->addError($this->t('Login failed, could not load user profile. Contact site administrator.'));

        return NULL;
      }

      return $profile;

    }
    catch (PluginException $exception) {
      $this->messenger->addError($this->t('There has been an error when creating plugin.'));

      return NULL;
    }
  }

}
