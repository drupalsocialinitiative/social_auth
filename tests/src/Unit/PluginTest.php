<?php

use Drupal\Tests\UnitTestCase;
use Drupal\social_auth\Plugin\Block\SocialAuthLoginBlock;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\social_auth\Plugin\Network\NetworkBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\social_api\Plugin\NetworkBase as SocialApiNetworkBase;


class NetworkTest extends UnitTestCase {

  protected $current_user;
  protected $plugin_id;
  protected $plugin_definition;
  protected $social_auth_config;
  protected $configuration;

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
   * tests for class SocialAuthLoginBlock
   */

  public function testSocialAuthLoginBlock () {
    $this->configuration = array();
    $this->social_auth_config = $this->createMock(ImmutableConfig::class);
    $socialAuthLoginBlock = $this->getMockBuilder(SocialAuthLoginBlock::class)
                       ->setConstructorArgs(array($this->configuration, $this->plugin_id, $this->plugin_definition, $this->social_auth_config))
                       ->getMock();
    $socialAuthLoginBlock->method('build')
               ->willReturn(['#theme' => 'login_with', '#social_networks' => $this->social_auth_config->get('auth')]);
    $this->assertTrue(
        method_exists($socialAuthLoginBlock, 'create'),
          'OAuth2Manager does not implements create function/method'
        );
    $this->assertTrue(
        method_exists($socialAuthLoginBlock, 'build'),
          'OAuth2Manager does not implements build function/method'
        );
    $this->assertEquals(['#theme' => 'login_with', '#social_networks' => $this->social_auth_config->get('auth')], $socialAuthLoginBlock->build());
  }

  /**
   * tests for class NetWorkbase
   */

  public function testNetworkBase () {
    $this->configuration = array();
    $this->plugin_definition = array();
    $setting = array();
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $config_factory = $this->createMock(ConfigFactoryInterface::class);
    $logger_factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $settings = new Settings($setting);
    // $settings = new ReflectionClass(Settings::class);
    $networkBase = $this->getMockBuilder(NetworkBase::class)
                       ->setConstructorArgs(array($this->configuration, $this->plugin_id, $this->plugin_definition, $entity_type_manager, $config_factory, $logger_factory, $settings))
                       ->getMockForAbstractClass();
    $this->assertTrue(
      method_exists($networkBase, 'create'),
        'NetworkBase does not implements create function/method'
      );
  }

}

 ?>
