<?php

use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\TestCase;
use Drupal\social_auth\SocialAuthDataHandler;
use Drupal\social_api\SocialApiDataHandler;

class SocialAuthDataHandlerTest extends UnitTestCase {

  /**
   * __construct function
   */
  public function __construct() {
       parent::__construct();
   }

  /**
   * {@inheritdoc}
   */

  public function setUp() {
    parent::setUp();
  }

  /**
   * tests for class SocialAuthDataHandler
   */

  public function testSocialAuthDataHandler () {

    $collection = $this->getMockBuilder(SocialAuthDataHandler::class)
                          ->disableOriginalConstructor()
                          ->getMock();
    $this->assertTrue($collection instanceof SocialAuthDataHandler);
  }
}
 ?>
