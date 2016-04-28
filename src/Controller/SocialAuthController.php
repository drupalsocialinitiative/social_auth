<?php
/**
 * @file
 * Contains Drupal\social_auth\Controller\SocialAuthController
 */

namespace Drupal\social_auth\Controller;

use Drupal\social_api\Controller\SocialApiController;

class SocialAuthController extends SocialApiController
{
  /**
   * Render the list of plugins for user authentication
   *
   * @param string $type
   * @return array
   */
  public function integrations($type = 'user_auth') {
    return parent::integrations($type);
  }

  /**
   * Set the settings for the login button for the given social networking
   *
   * @param $module
   * @param $route
   * @param $img_path
   */
  public static function setLoginButtonSettings($module, $route, $img_path) {
    $config = \Drupal::configFactory()->getEditable('social_api.settings');

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
    $config = \Drupal::configFactory()->getEditable('social_api.settings');;

    $config->clear('auth.' . $module)
      ->save();
  }
}
