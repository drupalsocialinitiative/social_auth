<?php

namespace Drupal\social_auth\Event;

use Drupal\social_auth\SocialAuthDataHandler;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Dispatched when user authentication fails in provider.
 *
 * @see \Drupal\social_auth\Event\SocialAuthEvents
 */
class FailedAuthenticationEvent extends Event {

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
   * The error string.
   *
   * @var string
   */
  protected $error;

  /**
   * RedirectResponse object.
   *
   * @var \Symfony\Component\HttpFoundation\RedirectResponse
   */
  protected $response;

  /**
   * FailedAuthenticationEvent constructor.
   *
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   The Social Auth data handler.
   * @param string $plugin_id
   *   The plugin Id dispatching this event.
   * @param string $error
   *   The error string.
   */
  public function __construct(SocialAuthDataHandler $data_handler, $plugin_id, $error = NULL) {
    $this->dataHandler = $data_handler;
    $this->pluginId = $plugin_id;
    $this->error = $error;
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
   * Gets the error string from provider.
   *
   * @return string
   *   The error string.
   */
  public function getError() {
    return $this->error;
  }

  /**
   * Returns the current redirect response object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response from the provider.
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * Sets a new redirect response object.
   *
   * @param \Symfony\Component\HttpFoundation\RedirectResponse $response
   *   The response from the provider.
   */
  public function setResponse(RedirectResponse $response) {
    $this->response = $response;
  }

  /**
   * Returns whether a redirect response was set.
   *
   * @return bool
   *   Whether a response was set.
   */
  public function hasResponse() {
    return $this->response !== NULL;
  }

}
