<?php

use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\TestCase;
//OAuth2Manager
use Drupal\social_auth\AuthManager\OAuth2Manager;
use Drupal\Core\Config\Config;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\social_api\AuthManager\OAuth2Manager as BaseOAuth2Manager;
use Drupal\social_auth\AuthManager\OAuth2ManagerInterface;


class AuthManagerTest extends UnitTestcase {

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


  public function testOAuth2Manager () {
    $logger_factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $settings = $this->createMock(Config::class);
    $collection = $this->getMockBuilder(OAuth2Manager::class)
                       ->setConstructorArgs(array($settings, $logger_factory))
                       ->setMethods(['getScopes', 'getEndPoints', 'settings', 'get'])
                       ->getMockForAbstractClass();

    $scopes = FALSE;
    $endPoints = "drupal123";
    $this->assertTrue(
          method_exists($collection, 'getExtraDetails'),
            'OAuth2Manager does not have getExtraDetails function/method'
    );
    $this->assertTrue(
          method_exists($collection, 'getScopes'),
            'OAuth2Manager does not have getScopes function/method'
    );
    $this->assertTrue(
          method_exists($collection, 'getEndPoints'),
            'OAuth2Manager does not have ggetEndPoints function/method'
    );

    $settings->method('get')
             ->willReturn('drupal123');

    if ($scopes === FALSE){
      $scopes = $settings->get('scopes');
    }

    $collection->method('getScopes')
                     ->willReturn($scopes);

    if ($endPoints === FALSE) {
      $endPoints = $settings->get('endpoints');
    }
    $collection->method('getEndPoints')
                     ->willReturn($endPoints);

    $this->assertSame('drupal123', $collection->getScopes());
    $this->assertSame('drupal123', $collection->getEndPoints());
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

  public function testOAuth2ManagerInterface () {
    $method = "drupalmethod";
    $path = "drupalpath";
    $collections = $this->getMock(OAuth2ManagerInterface::class);
    $this->assertTrue(
          method_exists($collections, 'getExtraDetails'),
            'OAuth2ManagerInterface does not have getExtraDetails function/method'
    );
    $this->assertTrue(
          method_exists($collections, 'requestEndPoint'),
            'OAuth2ManagerInterface does not have requestEndPoint function/method'
    );
    $this->assertTrue(
          method_exists($collections, 'getScopes'),
            'OAuth2ManagerInterface does not have getScopes function/method'
    );
    $this->assertTrue(
          method_exists($collections, 'getEndPoints'),
            'OAuth2ManagerInterface does not have getEndPoints function/method'
    );
    $options = array();
    $collections->requestEndPoint($method, $path, $domain = NULL, $options = []);
  }
}

 ?>
