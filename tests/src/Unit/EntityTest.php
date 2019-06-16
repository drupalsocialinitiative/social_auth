<?php

use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\TestCase;
use Drupal\social_auth\Entity\SocialAuth;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;

class EntityTest extends UnitTestCase {
   public function testSocialAuth () {
     $socialAuth = $this->createMock(SocialAuth::class);

     $socialAuth->method('setAdditionalData')
                ->with('data')
                ->will($this->returnValue('additionalData'));

     $socialAuth->method('setToken')
                ->with('token')
                ->will($this->returnValue('tokenEntity'));

     $socialAuth->method('setCreatedTime')
                ->with(1560161171)
                ->will($this->returnValue('2019-06-10T10:06:11+00:00'));

     $socialAuth->method('setChangedTime')
                ->with(1560161171)
                ->will($this->returnValue('2019-06-10T10:06:11+00:00'));

     $socialAuth->method('getAdditionalData')
                ->will($this->returnValue($socialAuth->setAdditionalData('data')));

     $socialAuth->method('getToken')
                ->will($this->returnValue($socialAuth->setToken('token')));

     $socialAuth->method('getCreatedTime')
                ->will($this->returnValue($socialAuth->setCreatedTime(1560161171)));

     $socialAuth->method('getChangedTime')
                ->will($this->returnValue($socialAuth->setChangedTime(1560161171)));

      $this->assertTrue(
        method_exists($socialAuth, 'getUserId'),
          'SocialAuth does not implements getUserId function/method'
        );

     $this->assertTrue(
        method_exists($socialAuth, 'setAdditionalData'),
          'SocialAuth does not implements setAdditionalData function/method'
        );

     $this->assertTrue(
        method_exists($socialAuth, 'getAdditionalData'),
          'SocialAuth does not implements getAdditionalData function/method'
        );

     $this->assertTrue(
        method_exists($socialAuth, 'setToken'),
          'SocialAuth does not implements setToken function/method'
        );

     $this->assertTrue(
        method_exists($socialAuth, 'getToken'),
          'SocialAuth does not implements getToken function/method'
        );

     $this->assertTrue(
        method_exists($socialAuth, 'setCreatedTime'),
          'SocialAuth does not implements setCreatedTime function/method'
        );

     $this->assertTrue(
        method_exists($socialAuth, 'getCreatedTime'),
          'SocialAuth does not implements getCreatedTime function/method'
        );

     $this->assertTrue(
        method_exists($socialAuth, 'setChangedTime'),
          'SocialAuth does not implements setChangedTime function/method'
        );

     $this->assertTrue(
        method_exists($socialAuth, 'getChangedTime'),
          'SocialAuth does not implements getChangedTime function/method'
        );

     $this->assertEquals('additionalData', $socialAuth->getAdditionalData());
     $this->assertEquals('tokenEntity', $socialAuth->getToken());
     $this->assertSame('2019-06-10T10:06:11+00:00', $socialAuth->getCreatedTime());
     $this->assertEquals('2019-06-10T10:06:11+00:00', $socialAuth->getChangedTime());
   }
}
