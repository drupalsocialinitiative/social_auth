<?php

namespace Drupal\social_auth\AuthManager;

use Drupal\Core\Config\Config;
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
   * Constructor.
   *
   * @param \Drupal\Core\Config\Config $settings
   *   The implementer settings.
   */
  public function __construct(Config $settings) {
    $this->settings = $settings;
    $this->endPoints = $this->scopes = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtraDetails() {
    $endpoints = $this->getEndPoints();

    // Store the data mapped with endpoints define in settings.
    $data = [];

    if ($endpoints) {
      // Iterate through api calls define in settings and retrieve them.
      foreach (explode(PHP_EOL, $endpoints) as $endpoint) {
        // Endpoint is set as path/to/endpoint|name.
        $parts = explode('|', $endpoint);
        $call[$parts[1]] = $this->requestEndPoint($parts[0]);
        array_push($data, $call);
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
