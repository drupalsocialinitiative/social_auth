<?php

namespace Drupal\Tests\social_auth\Functional;

use Drupal\Core\Url;
use Drupal\Tests\social_api\Functional\SocialApiTestBase;

/**
 * Defines a base class for testing Social Auth implementers.
 */
abstract class SocialAuthTestBase extends SocialApiTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['block', 'social_auth'];

  /**
   * The block entity.
   *
   * @var \Drupal\block\Entity\Block
   */
  protected $socialAuthLoginBlock;

  /**
   * The root of the path of the authentication route.
   *
   * @var string
   */
  protected $authRootPath = '/user/login/';

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp() {

    $this->adminUserPermissions = ['administer social api authentication'];
    $this->moduleType = 'social-auth';

    parent::setUp();

    $this->socialAuthLoginBlock = $this->drupalPlaceBlock('social_auth_login', [
      'label' => 'Social Auth Login',
      'id' => 'social_auth_login',
    ]);
    $this->socialAuthLoginBlock->getPlugin()->setConfigurationValue('label_display', 1);
    $this->socialAuthLoginBlock->save();
  }

  /**
   * Test if link to provider exists in login block.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  protected function checkLinkToProviderExists() {
    // Test for a non-authenticated user.
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->pageTextContains($this->socialAuthLoginBlock->label());

    if ($this->provider) {
      $this->checkPathInBlock($this->authRootPath . $this->provider);
    }

    // Test for an authenticated user.
    $this->drupalLogin($this->noPermsUser);
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->pageTextContains($this->socialAuthLoginBlock->label());

    if ($this->provider) {
      $this->checkPathInBlock($this->authRootPath . $this->provider);
    }
  }

  /**
   * Check if link to path is in the login block.
   *
   * @param string $path
   *   The path to the start of the authentication process.
   */
  protected function checkPathInBlock($path) {
    $links = $this->xpath('//a[contains(@href, :href)]', [':href' => $path]);

    $this->assertGreaterThanOrEqual(1, count($links));
  }

}
