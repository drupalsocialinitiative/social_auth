<?php

use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\TestCase;
use Drupal\social_auth\AuthManager\OAuth2Manager;
use Drupal\Core\Config\Config;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\social_api\AuthManager\OAuth2Manager as BaseOAuth2Manager;
use Drupal\social_auth\AuthManager\OAuth2ManagerInterface;

class AuthManagerTest extends UnitTestcase {

  /**
   * tests for class OAuth2Manager
   */
  public function testOAuth2Manager () {
    $scopes = FALSE;
    $endPoints = "drupal123";

    $logger_factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $settings = $this->createMock(Config::class);

    $authManager = $this->getMockBuilder(OAuth2Manager::class)
                       ->setConstructorArgs(array($settings, $logger_factory))
                       ->setMethods(['getScopes', 'getEndPoints', 'settings', 'get'])
                       ->getMockForAbstractClass();

    $this->assertTrue(
          method_exists($authManager, 'getExtraDetails'),
            'OAuth2Manager does not have getExtraDetails function/method'
    );

    $this->assertTrue(
          method_exists($authManager, 'getScopes'),
            'OAuth2Manager does not have getScopes function/method'
    );

    $this->assertTrue(
          method_exists($authManager, 'getEndPoints'),
            'OAuth2Manager does not have ggetEndPoints function/method'
    );

    $settings->method('get')
             ->willReturn('drupal123');

    if ($scopes === FALSE){
      $scopes = $settings->get('scopes');
    }
    $authManager->method('getScopes')
                     ->willReturn($scopes);

    if ($endPoints === FALSE) {
      $endPoints = $settings->get('endpoints');
    }
    $authManager->method('getEndPoints')
                     ->willReturn($endPoints);

    $this->assertSame('drupal123', $authManager->getScopes());
    $this->assertSame('drupal123', $authManager->getEndPoints());
  }

  // public function testGetExtraDetails ($method = 'GET', $domain = NULL) {
  //   $collection->method('getEndPoints')
  //                    ->willReturn($this->endPoints);
  //   $endpoints = $this->collection->getEndPoints();
  //   $data = [];
  //   if ($endpoints) {
  //     // Iterates through endpoints define in settings and retrieves them.
  //     foreach (explode(PHP_EOL, $endpoints) as $endpoint) {
  //       // Endpoint is set as path/to/endpoint|name.
  //       $parts = explode('|', $endpoint);
  //
  //       $data[$parts[0]] = $this->collection->requestEndPoint($method, $parts[0], $domain);
  //     }
  //     return json_encode($data);
  //   }
  // }

  /**
   * tests for clas OAuth2ManagerInterface
   */
  public function testOAuth2ManagerInterface () {
    $method = "drupalmethod";
    $path = "drupalpath";

    $authManagerInterface = $this->getMock(OAuth2ManagerInterface::class);

    $authManagerInterface->requestEndPoint($method, $path, $domain = NULL, $options = []);

    $this->assertTrue(
          method_exists($authManagerInterface, 'getExtraDetails'),
            'OAuth2ManagerInterface does not have getExtraDetails function/method'
    );

    $this->assertTrue(
          method_exists($authManagerInterface, 'requestEndPoint'),
            'OAuth2ManagerInterface does not have requestEndPoint function/method'
    );

    $this->assertTrue(
          method_exists($authManagerInterface, 'getScopes'),
            'OAuth2ManagerInterface does not have getScopes function/method'
    );

    $this->assertTrue(
          method_exists($authManagerInterface, 'getEndPoints'),
            'OAuth2ManagerInterface does not have getEndPoints function/method'
    );
  }
}
