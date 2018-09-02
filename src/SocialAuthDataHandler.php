<?php

namespace Drupal\social_auth;

use Drupal\social_api\SocialApiDataHandler;

/**
 * Variables are written to and read from session via this class.
 */
class SocialAuthDataHandler extends SocialApiDataHandler {

  /**
   * Forces the session to be saved and closed.
   */
  public function save() {
    $this->session->save();
  }

}
