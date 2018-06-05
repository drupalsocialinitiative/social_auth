<?php

namespace Drupal\Tests\social_auth\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Provides base test coverage for Social Auth.
 *
 * @group social_auth
 */
class SocialAuthBaseTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['block', 'social_auth'];

  /**
   * A test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

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
   * The machine name of the provider the module works with.
   *
   * @var null|string
   */
  protected $provider = NULL;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->user = $this->drupalCreateUser();

    $this->socialAuthLoginBlock = $this->drupalPlaceBlock('social_auth_login', [
      'label' => 'Social Auth Login',
      'id' => 'social_auth_login',
    ]);
    $this->socialAuthLoginBlock->getPlugin()->setConfigurationValue('label_display', 1);
    $this->socialAuthLoginBlock->save();
  }

  /**
   * Test if link to provider exists in login block.
   */
  protected function checkLinkToProviderExists() {
    // Test for a non-authenticated user.
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->pageTextContains($this->socialAuthLoginBlock->label());

    if ($this->provider) {
      $this->checkPathInBlock($this->authRootPath . $this->provider);
    }

    // Test for an authenticated user.
    $this->drupalLogin($this->user);
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
