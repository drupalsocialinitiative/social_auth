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

  protected $settings;
  protected $loggerFactory;
  protected $scopes;
  public $collection;
  protected $endPoints;
  public $collections;
  protected $method;
  protected $path;

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
    $this->logger_factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $this->settings = $this->createMock(Config::class);
    $this->collection = $this->getMockBuilder(OAuth2Manager::class)
                       ->setConstructorArgs(array($this->settings, $this->logger_factory))
                       ->setMethods(['getScopes', 'getEndPoints'])
                       ->getMockForAbstractClass();
    $this->collections = $this->getMock(OAuth2ManagerInterface::class);
    // enable any other required module
    $this->scopes = "drupal123";
    $this->endPoints = "drupal123";
    parent::setUp();
  }


  public function testOAuth2Manager () {
    $this->assertTrue(
          method_exists($this->collection, 'getExtraDetails'),
            'OAuth2Manager does not have getExtraDetails function/method'
    );
    $this->assertTrue(
          method_exists($this->collection, 'getScopes'),
            'OAuth2Manager does not have getScopes function/method'
    );
    $this->assertTrue(
          method_exists($this->collection, 'getEndPoints'),
            'OAuth2Manager does not have ggetEndPoints function/method'
    );

    $this->collection->method('getScopes')
                     ->willReturn($this->scopes);
    if ($this->scopes === FALSE){
      $this->scopes = $this->settings->get('scopes');
    }
    $this->assertSame('drupal123', $this->collection->getScopes());

    $this->collection->method('getEndPoints')
                     ->willReturn($this->endPoints);
    if ($this->endPoints === FALSE) {
      $this->endPoints = $this->settings->get('endpoints');
    }
    $this->assertSame('drupal123', $this->collection->getEndPoints());
  }

  // public function testGetExtraDetails ($method = 'GET', $domain = NULL) {
  //   $this->collection->method('getEndPoints')
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
    $this->assertTrue(
          method_exists($this->collections, 'getExtraDetails'),
            'OAuth2ManagerInterface does not have getExtraDetails function/method'
    );
    $this->assertTrue(
          method_exists($this->collections, 'requestEndPoint'),
            'OAuth2ManagerInterface does not have requestEndPoint function/method'
    );
    $this->assertTrue(
          method_exists($this->collections, 'getScopes'),
            'OAuth2ManagerInterface does not have getScopes function/method'
    );
    $this->assertTrue(
          method_exists($this->collections, 'getEndPoints'),
            'OAuth2ManagerInterface does not have getEndPoints function/method'
    );
    $options = array();
    $this->collections->requestEndPoint($this->method, $this->path, $domain = NULL, $options = []);
  }
}

 ?>
