<?php

use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\TestCase;
use Drupal\social_auth\SocialAuthDataHandler;
use Drupal\social_api\SocialApiDataHandler;

class SocialAuthDataHandlerTest extends UnitTestCase {

  public function testSocialAuthHandler () {

    $collection = $this->getMockBuilder('Drupal\social_auth\SocialAuthDataHandler')
                          ->disableOriginalConstructor()
                          ->getMock();
    $this->assertTrue($collection instanceof SocialAuthDataHandler);
  }
}
 ?>
