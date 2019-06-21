<?php

use Drupal\Tests\UnitTestCase;
use Drupal\social_auth\Controller\SocialAuthController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\social_auth\Controller\OAuth2ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\social_auth\AuthManager\OAuth2ManagerInterface;
use Drupal\social_auth\SocialAuthDataHandler;
use Drupal\social_auth\User\UserAuthenticator;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines Controller Test Class.
 */
class SocialAuthControllerTest extends UnitTestCase {

  /**
   * Tests for class SocialAuthController.
   */
  public function testSocialAuthController() {
    $namespaces = $this->createMock(Traversable::class);
    $cache_backend = $this->createMock(CacheBackendInterface::class);
    $module_handler = $this->createMock(ModuleHandlerInterface::class);
    $container = $this->createMock(ContainerInterface::class);

    $networkManager = $this->getMockBuilder(NetworkManager::class)
      ->setConstructorArgs([$namespaces, $cache_backend, $module_handler])
      ->getMock();

    $socialAuthController = $this->getMockBuilder(SocialAuthController::class)
      ->setConstructorArgs([$networkManager])
      ->setMethods(null)
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
   * Tests for class OAuth2ControllerBase.
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
      ->setConstructorArgs(['moduleName',
        'pluginId',
        $messenger,
        $network_manager,
        $user_authenticator,
        $provider_manager,
        $request,
        $data_handler,
        $renderer,
      ])
      ->setMethods(null)
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
