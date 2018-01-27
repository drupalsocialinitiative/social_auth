<?php

namespace Drupal\social_auth\AuthManager;

use Drupal\social_api\AuthManager\OAuth2ManagerInterface as BaseOAuth2ManagerInterface;

/**
 * Defines an OAuth2Manager Interface.
 *
 * @package Drupal\social_auth\AuthManager
 */
interface OAuth2ManagerInterface extends BaseOAuth2ManagerInterface {

  /**
   * Request data from the declared endpoints.
   *
   * @return array
   *   The extra details gotten from provider.
   */
  public function getExtraDetails();

  /**
   * Request and end point.
   *
   * @param string $path
   *   The path or url to request.
   *
   * @return array|mixed
   *   Data returned by provider.
   */
  public function requestEndPoint($path);

  /**
   * Gets the scopes defined in the settings form.
   *
   * @return string
   *   Data points separated by comma.
   */
  public function getScopes();

  /**
   * Gets the API endpoints to be requested.
   *
   * @return string
   *   API endpoints separated in different lines.
   */
  public function getEndPoints();

}
