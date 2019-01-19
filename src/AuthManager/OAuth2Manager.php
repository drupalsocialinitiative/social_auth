<?php

namespace Drupal\social_auth\AuthManager;

use Drupal\Core\Config\Config;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\social_api\AuthManager\OAuth2Manager as BaseOAuth2Manager;

/**
 * Defines a basic OAuth2Manager.
 *
 * @package Drupal\social_auth
 */
abstract class OAuth2Manager extends BaseOAuth2Manager implements OAuth2ManagerInterface {

  /**
   * Social Auth implementer settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $settings;

  /**
   * The scopes to be requested.
   *
   * @var string
   */
  protected $scopes;

  /**
   * The end points to be requested.
   *
   * @var string
   */
  protected $endPoints;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\Config $settings
   *   The implementer settings.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(Config $settings, LoggerChannelFactoryInterface $logger_factory) {
    $this->settings = $settings;
    $this->loggerFactory = $logger_factory;
    $this->endPoints = $this->scopes = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtraDetails($method = 'GET', $domain = NULL) {
    $endpoints = $this->getEndPoints();

    // Stores the data mapped with endpoints define in settings.
    $data = [];

    if ($endpoints) {
      // Iterates through endpoints define in settings and retrieves them.
      foreach (explode(PHP_EOL, $endpoints) as $endpoint) {
        // Endpoint is set as path/to/endpoint|name.
        $parts = explode('|', $endpoint);

        $data[$parts[1]] = $this->requestEndPoint($method, $parts[0], $domain);
      }

      return json_encode($data);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getScopes() {
    if ($this->scopes === FALSE) {
      $this->scopes = $this->settings->get('scopes');
    }

    return $this->scopes;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndPoints() {
    if ($this->endPoints === FALSE) {
      $this->endPoints = $this->settings->get('endpoints');
    }

    return $this->endPoints;
  }

}
