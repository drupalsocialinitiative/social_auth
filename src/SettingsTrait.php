<?php

namespace Drupal\social_auth;

use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Helper methods for Social Auth and Drupal settings.
 */
trait SettingsTrait {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Used to check if route path exists.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The Social Auth data handler.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  protected $dataHandler;

  /**
   * Checks if user registration is disabled.
   *
   * @return bool
   *   True if registration is disabled
   *   False if registration is not disabled
   */
  protected function isRegistrationDisabled() {
    // Check if Drupal account registration settings is Administrators only
    // OR if it is disabled in Social Auth Settings.
    return $this->configFactory->get('user.settings')->get('register') == 'admin_only'
      || $this->configFactory->get('social_auth.settings')->get('user_allowed') == 'login';
  }

  /**
   * Checks if admin approval is required for new users.
   *
   * @return bool
   *   True if approval is required
   *   False if approval is not required
   */
  protected function isApprovalRequired() {
    return $this->configFactory->get('user.settings')->get('register') == 'visitors_admin_approval';
  }

  /**
   * Checks if Admin (user 1) can login.
   *
   * @param \Drupal\user\UserInterface $drupal_user
   *   User object to check if user is admin.
   *
   * @return bool
   *   True if user 1 can't login.
   *   False otherwise
   */
  protected function isAdminDisabled(UserInterface $drupal_user) {
    return $this->configFactory->get('social_auth.settings')->get('disable_admin_login')
      && $drupal_user->id() == 1;
  }

  /**
   * Checks if User with specific roles is allowed to login.
   *
   * @param \Drupal\user\UserInterface $drupal_user
   *   User object to check if user has a specific role.
   *
   * @return string|false
   *   The role that can't login.
   *   False if the user roles are not disabled.
   */
  protected function isUserRoleDisabled(UserInterface $drupal_user) {
    foreach ($this->configFactory->get('social_auth.settings')->get('disabled_roles') as $role) {
      if ($drupal_user->hasRole($role)) {
        return $role;
      }
    }

    return FALSE;
  }

  /**
   * Checks if User should be redirected to User Form after creation.
   *
   * @param \Drupal\user\UserInterface $drupal_user
   *   User object to get the id of user.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|false
   *   A redirect response to user form, if option is enabled.
   *   False otherwise
   */
  protected function redirectToUserForm(UserInterface $drupal_user) {
    if ($this->configFactory->get('social_auth.settings')->get('redirect_user_form')) {

      $redirection = Url::fromRoute('entity.user.edit_form', [
        'user' => $drupal_user->id(),
      ]);

      return new RedirectResponse($redirection->toString());
    }

    return FALSE;
  }

  /**
   * Returns the status for new users.
   *
   * @return int
   *   Value 0 means that new accounts remain blocked and require approval.
   *   Value 1 means that visitors can register new accounts without approval.
   */
  protected function getNewUserStatus() {
    $allowed = $this->configFactory->get('user.settings')->get('register');
    return (int) ($allowed === 'visitors');
  }

  /**
   * Returns the Post Login redirection.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Post Login Path to which the user would be redirected after login.
   */
  protected function getPostLoginRedirection() {
    // Gets destination parameter previously stored in session.
    $destination = $this->dataHandler->get('login_destination');

    // If there was a destination parameter.
    if ($destination) {
      // Deletes the session key.
      $this->dataHandler->set('login_destination', NULL);

      return new RedirectResponse(Url::fromUserInput($destination)->toString());
    }

    $post_login = $this->configFactory->get('social_auth.settings')->get('post_login');

    return new RedirectResponse(Url::fromUserInput($post_login)->toString());
  }

}
