<?php

namespace Drupal\social_auth\Event;

use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\social_auth\User\SocialAuthUserInterface;

/**
 * Dispatched when user is created or logged in through Social Auth.
 *
 * @see \Drupal\social_auth\Event\SocialAuthEvents
 */
class UserEvent extends Event {

  /**
   * The user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;

  /**
   * The plugin id dispatching this event.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The user's data passed by Social Auth.
   *
   * @var \Drupal\social_auth\User\SocialAuthUserInterface
   */
  protected $socialAuthUser;

  /**
   * UserEvent constructor.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user.
   * @param string $plugin_id
   *   The plugin Id dispatching this event.
   * @param \Drupal\social_auth\User\SocialAuthUserInterface|null $social_auth_user
   *   The user's data passed by Social Auth.
   */
  public function __construct(UserInterface $user, $plugin_id, SocialAuthUserInterface $social_auth_user = NULL) {
    $this->user = $user;
    $this->pluginId = $plugin_id;
    $this->socialAuthUser = $social_auth_user;
  }

  /**
   * Gets the user.
   *
   * @return \Drupal\user\UserInterface
   *   The user.
   */
  public function getUser() {
    return $this->user;
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
   * Gets user's data passed by Social Auth.
   *
   * @return \Drupal\social_auth\User\SocialAuthUserInterface
   *   The user's data.
   */
  public function getSocialAuthUser() {
    return $this->socialAuthUser;
  }

}
