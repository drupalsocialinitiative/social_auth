<?php

namespace Drupal\social_auth;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Variables are written to and read from session via this class.
 */
class SocialAuthDataHandler {
  protected $session;
  protected $sessionPrefix;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   Used for reading data from and writing data to session.
   */
  public function __construct(SessionInterface $session) {
    $this->session = $session;
  }

  /**
   * Gets a session variable by key.
   *
   * @param string $key
   *   The session variable key.
   *
   * @return mixed
   *   The session variable value.
   */
  public function get($key) {
    return $this->session->get($this->getSessionPrefix() . $key);
  }

  /**
   * Sets a new session variable.
   *
   * @param string $key
   *   The session variable key.
   * @param mixed $value
   *   The session variable value.
   */
  public function set($key, $value) {
    $this->session->set($this->getSessionPrefix() . $key, $value);
  }

  /**
   * Gets the session prefix for the data handler.
   *
   * @return string
   *   The session prefix.
   */
  public function getSessionPrefix() {
    return $this->sessionPrefix;
  }

  /**
   * Sets the session prefix for the data handler.
   *
   * @param string $prefix
   *   The session prefix.
   */
  public function setSessionPrefix($prefix) {
    $this->sessionPrefix = $prefix . '_';
  }

}
