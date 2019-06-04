<?php

namespace Drupal\social_auth\User;

/**
 * User data used for authentication with Drupal.
 */
interface SocialAuthUserInterface {

  /**
   * Adds a new key-value pair in customData.
   *
   * @param string $key
   *   The key identifying the data.
   * @param mixed $value
   *   The value associated to the key.
   *
   * @return \Drupal\social_auth\User\User
   *   The User instance.
   */
  public function addData($key, $value);

  /**
   * Gets a value from customData.
   *
   * @param string $key
   *   The key identifying the data.
   *
   * @return mixed|null
   *   The custom data or null if not found.
   */
  public function getData($key);

}
