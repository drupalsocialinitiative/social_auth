<?php

namespace Drupal\social_auth\AuthManager;

use Drupal\social_api\AuthManager\OAuth2Manager as BaseOAuth2Manager;

/**
 * Defines a basic OAuth2Manager.
 *
 * @package Drupal\social_auth
 */
abstract class OAuth2Manager extends BaseOAuth2Manager implements OAuth2ManagerInterface {

  /**
   * The scopes to be requested.
   *
   * @var string|null
   */
  protected $scopes;

  /**
   * The end points to be requested.
   *
   * @var string|null
   */
  protected $endPoints;

  /**
   * The user returned by the provider.
   *
   * @var \League\OAuth2\Client\Provider\GenericResourceOwner|array|mixed
   */
  protected $user;

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

      return $data;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getScopes() {
    if ($this->scopes === NULL) {
      $this->scopes = $this->settings->get('scopes');
    }

    return $this->scopes;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndPoints() {
    if ($this->endPoints === NULL) {
      $this->endPoints = $this->settings->get('endpoints');
    }

    return $this->endPoints;
  }

}
