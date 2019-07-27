<?php

namespace Drupal\social_auth\User;

/**
 * User data used for authentication with Drupal.
 */
interface SocialAuthUserInterface {

  /**
   * Gets the user's first name.
   *
   * @return string
   *   The user's first name.
   */
  public function getFirstName();

  /**
   * Sets the user's first name.
   *
   * @param string $first_name
   *   The user's first name.
   */
  public function setFirstName($first_name);

  /**
   * Gets the user's last name.
   *
   * @return string
   *   The user's last name.
   */
  public function getLastName();

  /**
   * Sets the user's last name.
   *
   * @param string $last_name
   *   The user's last name.
   */
  public function setLastName($last_name);

  /**
   * Gets the user's name.
   *
   * @return string
   *   The user's name.
   */
  public function getName();

  /**
   * Sets the user's name.
   *
   * @param string $name
   *   The user's name.
   */
  public function setName($name);

  /**
   * Gets the user's email.
   *
   * @return string
   *   The user's email.
   */
  public function getEmail();

  /**
   * Sets the user's email.
   *
   * @param string $email
   *   The user's email.
   */
  public function setEmail($email);

  /**
   * Gets the user's id in provider.
   *
   * @return string
   *   The user's id in provider.
   */
  public function getProviderId();

  /**
   * Sets the user's id in provider.
   *
   * @param string $provider_id
   *   The user's id in provider.
   */
  public function setProviderId($provider_id);

  /**
   * Gets the user's token.
   *
   * @return string
   *   The user's token.
   */
  public function getToken();

  /**
   * Sets the user's token.
   *
   * @param string $token
   *   The user's token.
   */
  public function setToken($token);

  /**
   * Gets the user's picture URL.
   *
   * @return string
   *   The user's picture URL.
   */
  public function getPictureUrl();

  /**
   * Sets the user's picture URL.
   *
   * @param string $picture_url
   *   The user's picture URL.
   */
  public function setPictureUrl($picture_url);

  /**
   * Gets the user's picture ID.
   *
   * @return string|int|null
   *   The user's picture ID.
   */
  public function getPicture();

  /**
   * Sets the user's picture ID.
   *
   * @param string|int|null $file_id
   *   The user's picture ID.
   */
  public function setPicture($file_id);

  /**
   * Set the user's additional data.
   *
   * @return array|null
   *   The user's additional data.
   */
  public function getAdditionalData();

  /**
   * Sets the user's additional data.
   *
   * @param array|null $additional_data
   *   The user's additional data.
   */
  public function setAdditionalData($additional_data);

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
