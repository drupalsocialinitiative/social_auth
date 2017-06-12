<?php

namespace Drupal\social_auth\AuthManager;

use Drupal\social_api\BaseManager;
/**
 * Defines an OAuth2Manager Interface.
 *
 * @package Drupal\social_auth\AuthManager
 */
interface OAuth2ManagerInterface extends BaseManager\BaseManagerInterface {

  /**
   * Returns the user and related information.
   *
   * @return mixed
   *   The user data.
   */
  public function getUserDetails();

}
