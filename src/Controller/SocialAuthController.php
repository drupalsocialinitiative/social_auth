<?php

namespace Drupal\social_auth\Controller;

use Drupal\social_api\Controller\SocialApiController;

/**
 * Provides methods to add or remove default login buttons
 */
class SocialAuthController extends SocialApiController {
  /**
   * Render the list of plugins for user authentication
   *
   * @param string $type
   * @return array
   */
  public function integrations($type = 'social_auth') {
    return parent::integrations($type);
  }

  /**
   * Set the settings for the login button for the given social networking
   *
   * The module machine name that implements its login button settings
   * @param string $module
   *
   * The route name of the user authentication controller
   * @param string $route
   *
   * The path of the image for login
   * @param string $img_path
   */
  public static function setLoginButtonSettings($module, $route, $img_path) {
    $config = \Drupal::configFactory()->getEditable('social_auth.settings');

    $img_path = drupal_get_path('module', $module) . '/' . $img_path;

    $config->set('auth.' . $module . '.route', $route)
      ->set('auth.' . $module . '.img_path', $img_path)
      ->save();
  }

  /**
   * Delete the settings for the login button for the given social networking
   *
   * @param $module
   */
  public static function deleteLoginButtonSettings($module) {
    $config = \Drupal::configFactory()->getEditable('social_auth.settings');;

    $config->clear('auth.' . $module)
      ->save();
  }
}
