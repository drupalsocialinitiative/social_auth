<?php

namespace Drupal\Tests\social_auth\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests Social Auth user related tasks.
 *
 * @group social_auth
 */
class UserTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['social_auth'];

  /**
   * The Drupal user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * The Social Auth user authenticator.
   *
   * @var \Drupal\social_auth\User\UserAuthenticator
   */
  protected $userAuthenticator;

  /**
   * The Drupal entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->user = $this->drupalCreateUser();

    $this->userAuthenticator = \Drupal::getContainer()->get('social_auth.user_authenticator');

    $this->entityTypeManager = \Drupal::entityTypeManager();
  }

  /**
   * Tests the case when Drupal user is deleted.
   *
   * When a Drupal user is removed, the associated Social Auth accounts should
   * also be removed.
   */
  public function testUserDeletion() {

    $this->drupalLogin($this->user);

    $uid = $this->user->id();

    // Associates a provider.
    $this->userAuthenticator->setPluginId('social_auth_provider1');
    $this->userAuthenticator->associateNewProvider('provider_id_123', 'token123', NULL);

    // Associates another provider.
    $this->userAuthenticator->setPluginId('social_auth_provider2');
    $this->userAuthenticator->associateNewProvider('provider_id_123', 'token123', NULL);

    try {
      $social_auth_storage = $this->entityTypeManager->getStorage('social_auth');

      $social_auth_users = $social_auth_storage->loadByProperties([
        'user_id' => $uid,
      ]);

      // Expects that the user has two associated providers.
      $this->assertEquals(2, count($social_auth_users), 'Number of associated providers should be 2');

      // Deletes the Drupal user.
      $this->entityTypeManager->getStorage('user')->delete([$this->user]);

      $social_auth_users = $social_auth_storage->loadByProperties([
        'user_id' => $uid,
      ]);

      // Expects that the user has no associated providers now.
      $this->assertEquals(0, count($social_auth_users), 'Number of associated providers should be 0');
    }
    catch (\Exception $e) {
      $this->fail($e->getMessage());
    }

  }

}
