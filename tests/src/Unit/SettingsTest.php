<?php

use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\TestCase;
use Drupal\social_auth\Settings\SettingsBase;
use Drupal\social_api\Settings\SettingsBase as SocialApiSettingsBase;
use Drupal\Core\Config\ImmutableConfig;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\social_auth\Settings\SettingsInterface;
use Drupal\social_api\Settings\SettingsInterface as SettingsInterfaceBase;


class SettingsTest extends UnitTestCase {

  protected $storage;
  protected $event_dispatched;
  protected $typed_config;
  protected $event_dispatcher;
  protected $config;

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
   * tests for class SettingsBase
   */

  public function testSettingsBase () {
    $this->storage = $this->createMock(StorageInterface::class);
    $this->event_dispatcher = $this->createMock(EventDispatcherInterface::class);
    $this->typed_config = $this->createMock(TypedConfigManagerInterface::class);
    $this->configs = $this->getMockBuilder(ImmutableConfig::class)
                          ->setConstructorArgs(array($this->config, $this->storage, $this->event_dispatcher, $this->typed_config))
                          ->getMock();
    $collection = $this->getMockBuilder(SettingsBase::class)
                       ->setConstructorArgs(array($this->configs))
                       ->getMock();
    $this->assertTrue($collection instanceof SettingsBase);
  }

  /**
   * tests for class SettingsInterface
   */

  public function testSettingsInterface () {
    $collection = $this->createMock(SettingsInterface::class);
    $this->assertTrue($collection instanceof SettingsInterface);
  }

}

 ?>
