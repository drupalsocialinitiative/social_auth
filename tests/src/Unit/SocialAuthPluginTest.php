<?php

use Drupal\Tests\UnitTestCase;
use Drupal\social_auth\Plugin\Block\SocialAuthLoginBlock;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\social_auth\Plugin\Network\NetworkBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;

/**
 * Defines Test Class for Plugin.
 */
class SocialAuthNetworkTest extends UnitTestCase {

  /**
   * Tests for class SocialAuthLoginBlock.
   */
  public function testSocialAuthLoginBlock() {
    $configuration = [];
    $social_auth_config = $this->createMock(ImmutableConfig::class);

    $socialAuthLoginBlock = $this->getMockBuilder(SocialAuthLoginBlock::class)
      ->setConstructorArgs([$configuration,
        'drupalPlugin',
        'definitionOfPlugin',
        $social_auth_config,
      ])
      ->getMock();

    $socialAuthLoginBlock->method('build')
      ->willReturn(['#theme' => 'login_with', '#social_networks' => $social_auth_config->get('auth')]);

    $this->assertTrue(
        method_exists($socialAuthLoginBlock, 'create'),
          'SocialAuthLoginBlock class does not implements create function/method'
        );

    $this->assertTrue(
        method_exists($socialAuthLoginBlock, 'build'),
          'SocialAuthLoginBlock class does not implements build function/method'
        );

    $this->assertEquals(['#theme' => 'login_with', '#social_networks' => $social_auth_config->get('auth')], $socialAuthLoginBlock->build());
  }

  /**
   * Tests for class NetWorkbase.
   */
  public function testNetworkBase() {
    $configuration = [];
    $plugin_definition = [];
    $setting = [];

    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $config_factory = $this->createMock(ConfigFactoryInterface::class);
    $logger_factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $settings = new Settings($setting);

    $networkBase = $this->getMockBuilder(NetworkBase::class)
      ->setConstructorArgs([$configuration,
        'drupalPlugin123',
        $plugin_definition,
        $entity_type_manager,
        $config_factory,
        $logger_factory,
        $settings,
      ])
      ->getMockForAbstractClass();

    $this->assertTrue(
      method_exists($networkBase, 'create'),
        'NetworkBase does not implements create function/method'
      );
  }

}
