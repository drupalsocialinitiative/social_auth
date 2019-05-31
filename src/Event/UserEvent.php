<?php

namespace Drupal\social_auth\Event;

use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;

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
   * UserEvent constructor.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user.
   * @param string $plugin_id
   *   The plugin Id dispatching this event.
   */
  public function __construct(UserInterface $user, $plugin_id) {
    $this->user = $user;
    $this->pluginId = $plugin_id;
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

}
