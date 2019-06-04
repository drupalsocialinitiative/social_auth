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
  public $firstName;

  /**
   * Last name.
   *
   * @var string|null
   */
  public $lastName;

  /**
   * Used to create the username in Drupal: first + last most of the time.
   *
   * @var string
   */
  public $name;

  /**
   * Email address.
   *
   * @var string|null
   */
  public $email;

  /**
   * ID in provider.
   *
   * @var string
   */
  public $providerUserID;

  /**
   * Token used for authentication in provider.
   *
   * @var string|mixed
   */
  public $token;

  /**
   * URL to get profile picture.
   *
   * @var string
   */
  public $pictureUrl = NULL;

  /**
   * User's extra data. Store in additional_data field in social_auth entity.
   *
   * @var string|null
   */
  public $additionalData;

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
   * @param string $additional_data
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
