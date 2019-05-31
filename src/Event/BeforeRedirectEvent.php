<?php

namespace Drupal\social_auth\Event;

use Drupal\social_auth\SocialAuthDataHandler;
use Symfony\Component\EventDispatcher\Event;

/**
 * Dispatched before user is redirected to provider for authentication.
 *
 * @see \Drupal\social_auth\Event\SocialAuthEvents
 */
class BeforeRedirectEvent extends Event {

  /**
   * The Social Auth data handler.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  protected $dataHandler;

  /**
   * The plugin id dispatching this event.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The destination where use will redirect after successful authentication.
   *
   * @var string
   */
  protected $destination;

  /**
   * BeforeRedirectEvent constructor.
   *
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   The Social Auth data handler.
   * @param string $plugin_id
   *   The plugin Id dispatching this event.
   * @param string $destination
   *   The destination where user will redirect after successful authentication.
   */
  public function __construct(SocialAuthDataHandler $data_handler, $plugin_id, $destination = NULL) {
    $this->dataHandler = $data_handler;
    $this->pluginId = $plugin_id;
    $this->destination = $destination;
  }

  /**
   * Gets the Social Auth data handler object.
   *
   * @return \Drupal\social_auth\SocialAuthDataHandler
   *   The Social Auth data handler.
   */
  public function getDataHandler() {
    return $this->dataHandler;
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
   * Gets the destination.
   *
   * @return string
   *   The destination path.
   */
  public function getDestination() {
    return $this->destination;
  }

}
