<?php

namespace Drupal\social_auth\Controller;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
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
   * The renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

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
   *   Used to get an instance of the network plugin.
   * @param \Drupal\social_auth\User\UserAuthenticator $user_authenticator
   *   Used to manage user authentication/registration.
   * @param \Drupal\social_auth\AuthManager\OAuth2ManagerInterface $provider_manager
   *   Used to manage authentication methods.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Used to access GET parameters.
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   The Social Auth data handler.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Used to handle metadata for redirection to authentication URL.
   */
  public function __construct($module,
                              $plugin_id,
                              MessengerInterface $messenger,
                              NetworkManager $network_manager,
                              UserAuthenticator $user_authenticator,
                              OAuth2ManagerInterface $provider_manager,
                              RequestStack $request,
                              SocialAuthDataHandler $data_handler,
                              RendererInterface $renderer = NULL) {

    $this->module = $module;
    $this->pluginId = $plugin_id;
    $this->messenger = $messenger;
    $this->networkManager = $network_manager;
    $this->userAuthenticator = $user_authenticator;
    $this->providerManager = $provider_manager;
    $this->request = $request;
    $this->dataHandler = $data_handler;
    $this->renderer = $renderer;

    /*
     * TODO: Added for backward compatibility.
     *
     * Remove after implementers have been updated.
     * @see https://www.drupal.org/project/social_auth/issues/3033444
     */
    if (!$this->renderer) {
      $this->renderer = \Drupal::service('renderer');
    }

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
   * Redirects the user to provider for authentication.
   *
   * This is done in a render context in order to bubble cacheable metadata
   * created during authentication URL generation.
   *
   * @see https://www.drupal.org/project/social_auth/issues/3033444
   */
  public function redirectToProvider() {
    $context = new RenderContext();

    /** @var \Drupal\Core\Routing\TrustedRedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse $response */
    $response = $this->renderer->executeInRenderContext($context, function () {
      try {
        /* @var \League\OAuth2\Client\Provider\AbstractProvider|false $client */
        $client = $this->networkManager->createInstance($this->pluginId)->getSdk();

        // If provider client could not be obtained.
        if (!$client) {
           $this->messenger->addError($this->t('%module not configured properly. Contact site administrator.', ['%module' => $this->module]));
           return $this->redirect('user.login');
        }

        /*
         * If destination parameter is set, save it.
         *
         * The destination parameter is also _removed_ from the current request
         * to prevent it from overriding Social Auth's TrustedRedirectResponse.
         *
         * @see https://www.drupal.org/project/drupal/issues/2950883
         *
         * TODO: Remove the remove() call after 2950883 is solved.
         */
        $destination = $this->request->getCurrentRequest()->get('destination');
        if ($destination) {
          $this->userAuthenticator->setDestination($destination);
          $this->request->getCurrentRequest()->query->remove('destination');
        }

        // Provider service was returned, inject it to $providerManager.
        $this->providerManager->setClient($client);

        // Generates the URL for authentication.
        $auth_url = $this->providerManager->getAuthorizationUrl();

        $state = $this->providerManager->getState();
        $this->dataHandler->set('oauth2state', $state);

        return new TrustedRedirectResponse($auth_url);
      }
      catch (PluginException $exception) {
        $this->messenger->addError($this->t('There has been an error when creating plugin.'));

        return $this->redirect('user.login');
      }
    });

    // Add bubbleable metadata to the response.
    if ($response instanceof TrustedRedirectResponse && !$context->isEmpty()) {
      $bubbleable_metadata = $context->pop();
      $response->addCacheableDependency($bubbleable_metadata);
    }

    return $response;
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
