<?php

namespace Drupal\social_auth\Event;

use Symfony\Component\EventDispatcher\Event;
use Drupal\social_auth\User\SocialAuthUserInterface;

/**
 * Defines the user fields to be set in user creation.
 *
 * @todo validate user_fields to be set
 *
 * @see \Drupal\social_auth\Event\SocialAuthEvents
 */
class UserFieldsEvent extends Event {

  /**
   * The user fields.
   *
   * @var array
   */
  protected $userFields;

  /**
   * The plugin id dispatching this event.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The data of the user to be created.
   *
   * @var \Drupal\social_auth\User\SocialAuthUserInterface
   */
  protected $user;

  /**
   * UserFieldsEvent constructor.
   *
   * @param array $user_fields
   *   Initial user fields to populate the newly created user.
   * @param string $plugin_id
   *   The plugin Id dispatching this event.
   * @param \Drupal\social_auth\User\SocialAuthUserInterface $user
   *   The data of the user to be created.
   */
  public function __construct(array $user_fields, $plugin_id, SocialAuthUserInterface $user) {
    $this->userFields = $user_fields;
    $this->pluginId = $plugin_id;
    $this->user = $user;
  }

  /**
   * Gets the user fields.
   *
   * @return array
   *   Fields to initialize for the user creation.
   */
  public function getUserFields() {
    return $this->userFields;
  }

  /**
   * Sets the user fields.
   *
   * @param array $user_fields
   *   The user fields.
   */
  public function setUserFields(array $user_fields) {
    $this->userFields = $user_fields;
  }

  /**
   * Gets the plugin id dispatching this event.
   *
   * @return string
   *   The plugin id.
   */
  public function getPluginId() {
    return $this->pluginId;
  }

  /**
   * Gets the data of the user to be created.
   *
   * @return \Drupal\social_auth\User\SocialAuthUserInterface
   *   The user's data.
   */
  public function getSocialAuthUser() {
    return $this->user;
  }

}
