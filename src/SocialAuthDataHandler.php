<?php

namespace Drupal\social_auth;
use Drupal\social_api\SocialApiDataHandler;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Variables are written to and read from session via this class.
 */
class SocialAuthDataHandler extends SocialApiDataHandler {
  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   Used for reading data from and writing data to session.
   */
  public function __construct(SessionInterface $session) {
    parent::__construct($session);
  }
}
