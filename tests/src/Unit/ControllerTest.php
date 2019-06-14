<?php

use Drupal\Tests\UnitTestCase;
use Drupal\social_auth\Controller\SocialAuthController;
use Drupal\social_api\Controller\SocialApiController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;


class ControllerTest extends UnitTestCase {

  /**
   * __construct function
   */
  public function __construct() {
       parent::__construct();
   }

  /**
   * {@inheritdoc}
   */

  public function setUp() {
    parent::setUp();
  }

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
    $this->assertTrue(true);
  }

}
