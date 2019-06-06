<?php

use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\TestCase;
use Drupal\social_auth\Settings\SettingsBase;
use Drupal\social_api\Settings\SettingsBase as SocialApiSettingsBase;
use Drupal\Core\Config\ImmutableConfig;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;


class SettingsTest extends UnitTestCase {

  protected $storage;
  protected $event_dispatched;
  protected $typed_config;
  protected $event_dispatcher;
  protected $config;

  public function testSettingsBase () {
    $this->storage = $this->createMock(StorageInterface::class);
    $this->event_dispatcher = $this->createMock(EventDispatcherInterface::class);
    $this->typed_config = $this->createMock(TypedConfigManagerInterface::class);
    $this->configs = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
                          ->setConstructorArgs(array($this->config, $this->storage, $this->event_dispatcher, $this->typed_config))
                          ->getMock();
    $collection = $this->getMockBuilder('Drupal\social_auth\Settings\SettingsBase')
                       ->setConstructorArgs(array($this->configs))
                       ->getMock();
    $this->assertTrue($collection instanceof SettingsBase);
  }
}

 ?>
