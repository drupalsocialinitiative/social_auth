<?php

use Drupal\Tests\UnitTestCase;
use Drupal\social_auth\Controller\SocialAuthController;
use Drupal\social_api\Controller\SocialApiController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\social_auth\Controller\OAuth2ControllerBase;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\social_auth\AuthManager\OAuth2ManagerInterface;
use Drupal\social_auth\SocialAuthDataHandler;
use Drupal\social_auth\User\UserAuthenticator;
use Symfony\Component\HttpFoundation\RequestStack;


class ControllerTest extends UnitTestCase {

  /**
   * tests for class SocialAuthController
   */
  public function testSocialAuthController () {
    $namespaces = $this->createMock(Traversable::class);
    $cache_backend = $this->createMock(CacheBackendInterface::class);
    $module_handler = $this->createMock(ModuleHandlerInterface::class);
    $container = $this->createMock(ContainerInterface::class);

    $networkManager = $this->getMockBuilder(NetworkManager::class)
      ->setConstructorArgs(array($namespaces, $cache_backend, $module_handler))
      ->getMock();

    $socialAuthController = $this->getMockBuilder(SocialAuthController::class)
      ->setConstructorArgs(array($networkManager))
      ->getMock();

    $this->assertTrue(
      method_exists($socialAuthController, 'setLoginButtonSettings'),
      'SocialAuthController does not have setLoginButtonSettings function/method'
      );

    $this->assertTrue(
      method_exists($socialAuthController, 'deleteLoginButtonSettings'),
      'SocialAuthController does not have deleteLoginButtonSettings function/method'
      );
  }

  /**
   * tests for class OAuth2ControllerBase
   */
  public function testOAuth2ControllerBase() {
    $messenger = $this->createMock(MessengerInterface::class);
    $network_manager = $this->createMock(NetworkManager::class);
    $user_authenticator = $this->createMock(UserAuthenticator::class);
    $provider_manager = $this->createMock(OAuth2ManagerInterface::class);
    $data_handler = $this->createMock(SocialAuthDataHandler::class);
    $renderer = $this->createMock(RendererInterface::class);
    $request = $this->createMock(RequestStack::class);

    $oAuth2ControllerBase = $this->getMockBuilder(OAuth2ControllerBase::class)
                                 ->setConstructorArgs(array('moduleName',
                                                      'pluginId',
                                                      $messenger,
                                                      $network_manager,
                                                      $user_authenticator,
                                                      $provider_manager,
                                                      $request,
                                                      $data_handler,
                                                      $renderer,
                                                      ))
                                 ->getMock();

     $this->assertTrue(
       method_exists($oAuth2ControllerBase, 'processCallback'),
         'OAuth2ControllerBase does not implements processCallback function/method'
       );

    $this->assertTrue(
       method_exists($oAuth2ControllerBase, 'redirectToProvider'),
         'OAuth2ControllerBase does not implements redirectToProvider function/method'
       );
  }

}
