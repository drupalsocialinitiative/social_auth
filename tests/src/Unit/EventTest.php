<?php

use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\TestCase;
use Drupal\social_auth\Event\BeforeRedirectEvent;
use Drupal\social_auth\SocialAuthDataHandler;
use Symfony\Component\EventDispatcher\Event;



class EventTest extends UnitTestCase {
  protected $data_handler;
  protected $pluginId = 'drupal123';
  protected $destination = 'drupal123';


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
   * test for class testBeforeRedirectEvent
   */

  public function testBeforeRedirectEvent () {
    $this->data_handler = $this->createMock(SocialAuthDataHandler::class);
    $collection = $this->getMockBuilder('Drupal\social_auth\Event\BeforeRedirectEvent')
                       ->setConstructorArgs(array($this->data_handler, $this->pluginId, $this->destination))
                       ->setMethods(array('getDataHandler', 'getPluginId', 'getDestination'))
                       ->getMock();

    $this->assertTrue(
        method_exists($collection, 'getDataHandler'),
        'BeforeRedirectEvent does not have getDataHandler function/method'
      );
    $this->assertTrue(
      method_exists($collection, 'getPluginId'),
      'BeforeRedirectEvent does not have getPluginId function/method'
    );
    $this->assertTrue(
      method_exists($collection, 'getDestination'),
      'BeforeRedirectEvent does not have getDestination function/method'
    );
    $collection->method('getPluginId')
               ->willReturn($this->pluginId);
    $collection->method('getDestination')
               ->willReturn($this->destination);
    $collection->method('getDataHandler')
               ->willReturn($this->data_handler);
    $this->assertSame('drupal123', $collection->getPluginId());
    $this->assertSame('drupal123', $collection->getDestination());
  }

}


 ?>
