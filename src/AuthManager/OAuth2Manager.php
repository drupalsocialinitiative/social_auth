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
   * Gets the scopes defined in the settings form.
   *
   * @return string
   *   Data points separated by comma.
   */
  public function getScopes() {
    if ($this->scopes === FALSE) {
      $this->scopes = $this->settings->get('scopes');
    }
    return $this->scopes;
  }

  /**
   * Gets the API endpoints to be requested.
   *
   * @return string
   *   API endpoints separated in different lines.
   */
  public function getEndPoints() {
    if ($this->endPoints === FALSE) {
      $this->endPoints = $this->settings->get('endpoints');
    }
    return $this->endPoints;
  }

}
