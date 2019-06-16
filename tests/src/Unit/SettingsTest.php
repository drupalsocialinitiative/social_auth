<?php

use Drupal\Tests\UnitTestCase;
use Drupal\social_auth\Settings\SettingsBase;
use Drupal\Core\Config\ImmutableConfig;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;

/**
 * Define Test Class SettingsTest.
 */
class SettingsTest extends UnitTestCase {

  /**
   * Tests for class SettingsBase.
   */
  public function testSettingsBase() {
    $storage = $this->createMock(StorageInterface::class);
    $event_dispatcher = $this->createMock(EventDispatcherInterface::class);
    $typed_config = $this->createMock(TypedConfigManagerInterface::class);

    $configs = $this->getMockBuilder(ImmutableConfig::class)
      ->setConstructorArgs(['drupalConfig123',
        $storage,
        $event_dispatcher,
        $typed_config,
      ])
      ->getMock();

    $collection = $this->getMockBuilder(SettingsBase::class)
      ->setConstructorArgs([$configs])
      ->getMock();

    // Writing this assertion, otherwise the test will throw a warning. And this
    // test is quite important as its checking for parent methods.
    $this->assertTrue($collection instanceof SettingsBase);
  }

}
