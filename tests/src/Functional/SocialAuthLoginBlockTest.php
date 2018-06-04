<?php

namespace Drupal\Tests\social_auth\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Test Social Auth Login block.
 *
 * @group social_auth
 */
class SocialAuthLoginBlockTest extends BrowserTestBase {

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
   * Test that the block is showing up.
   */
  public function testBlockExists() {
    // Test for a non-authenticated user.
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->pageTextContains($this->socialAuthLoginBlock->label());

    // Test for an authenticated user.
    $this->drupalLogin($this->user);
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->pageTextContains($this->socialAuthLoginBlock->label());
  }

}
