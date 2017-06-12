<?php

namespace Drupal\social_auth\OAuth2Manager;

use Drupal\social_api\OAuth2Manager\OAuth2ManagerInterface as BaseOAuth2ManagerInterface;

/**
 * Defines an OAuth2Manager Interface.
 *
 * @package Drupal\social_auth\AuthManager
 */
interface OAuth2ManagerInterface extends BaseOAuth2ManagerInterface {

  /**
   * Returns the user and related information.
   *
   * @return mixed
   *   The user data.
   */
  public function getUserInfo();

}
