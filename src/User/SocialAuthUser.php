<?php

namespace Drupal\social_auth\User;

/**
 * User data used for authentication with Drupal.
 */
class SocialAuthUser implements SocialAuthUserInterface {

  /**
   * First name.
   *
   * @var string|null
   */
  protected $firstName;

  /**
   * Last name.
   *
   * @var string|null
   */
  protected $lastName;

  /**
   * Used to create the username in Drupal: first + last most of the time.
   *
   * @var string
   */
  protected $name;

  /**
   * Email address.
   *
   * @var string|null
   */
  protected $email;

  /**
   * ID in provider.
   *
   * @var string
   */
  protected $providerUserID;

  /**
   * Token used for authentication in provider.
   *
   * @var string|mixed
   */
  protected $token;

  /**
   * URL to get profile picture.
   *
   * @var string
   */
  protected $pictureUrl = NULL;

  /**
   * Profile picture file.
   *
   * @var string|int|null
   */
  protected $picture = NULL;

  /**
   * User's extra data. Store in additional_data field in social_auth entity.
   *
   * @var array|null
   */
  protected $additionalData;

  /**
   * Other data added through external modules (e.g. event subscribers)
   *
   * @var array
   */
  protected $customData;

  /**
   * User constructor.
   *
   * @param string $name
   *   The user's name.
   * @param string $email
   *   The user's email address.
   * @param string $provider_user_id
   *   The unique ID in provider.
   * @param string $token
   *   The access token for making API calls.
   * @param string|bool $picture_url
   *   The user's picture.
   * @param array|null $additional_data
   *   The additional user data to be stored in database.
   */
  public function __construct($name,
                              $email,
                              $provider_user_id,
                              $token,
                              $picture_url = NULL,
                              $additional_data = NULL) {

    $this->name = $name;
    $this->email = $email;
    $this->providerUserID = $provider_user_id;
    $this->token = $token;
    $this->pictureUrl = $picture_url;
    $this->additionalData = $additional_data;
  }

  /**
   * {@inheritdoc}
   */
  public function getFirstName() {
    return $this->firstName;
  }

  /**
   * {@inheritdoc}
   */
  public function setFirstName($first_name) {
    $this->firstName = $first_name;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastName() {
    return $this->lastName;
  }

  /**
   * {@inheritdoc}
   */
  public function setLastName($last_name) {
    $this->lastName = $last_name;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->name = $name;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmail() {
    return $this->email;
  }

  /**
   * {@inheritdoc}
   */
  public function setEmail($email) {
    $this->email = $email;
  }

  /**
   * {@inheritdoc}
   */
  public function getProviderId() {
    return $this->providerUserID;
  }

  /**
   * {@inheritdoc}
   */
  public function setProviderId($provider_id) {
    $this->providerUserID = $provider_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getToken() {
    return $this->token;
  }

  /**
   * {@inheritdoc}
   */
  public function setToken($token) {
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public function getPictureUrl() {
    return $this->pictureUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function setPictureUrl($picture_url) {
    $this->pictureUrl = $picture_url;
  }

  /**
   * {@inheritdoc}
   */
  public function getPicture() {
    return $this->picture;
  }

  /**
   * {@inheritdoc}
   */
  public function setPicture($file_id) {
    $this->picture = $file_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getAdditionalData() {
    return $this->additionalData;
  }

  /**
   * {@inheritdoc}
   */
  public function setAdditionalData($additional_data) {
    $this->additionalData = $additional_data;
  }

  /**
   * {@inheritdoc}
   */
  public function addData($key, $value) {
    $this->customData[$key] = $value;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getData($key) {
    return $this->customData[$key] ?? NULL;
  }

}
