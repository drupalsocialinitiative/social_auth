<?php

use Drupal\Tests\UnitTestCase;
use Drupal\social_auth\Event\BeforeRedirectEvent;
use Drupal\social_auth\SocialAuthDataHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\social_auth\Event\FailedAuthenticationEvent;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\user\UserInterface;
use Drupal\social_auth\Event\UserEvent;
use Drupal\social_auth\Event\UserFieldsEvent;

/**
 * Defines Test class for Event.
 */
class EventTest extends UnitTestCase {

  /**
   * Tests for class testBeforeRedirectEvent.
   */
  public function testBeforeRedirectEvent() {

    $pluginId = 'drupal123';
    $destination = 'drupal123';
    $data_handler = $this->createMock(SocialAuthDataHandler::class);

    $beforeRedirectEvent = $this->getMockBuilder(BeforeRedirectEvent::class)
      ->setConstructorArgs([$data_handler,
        $pluginId,
        $destination,
      ])
      ->setMethods(null)
      ->getMock();


    $this->assertTrue(
      method_exists($beforeRedirectEvent, 'getDataHandler'),
      'BeforeRedirectEvent class does not implements getDataHandler function/method'
      );

    $this->assertTrue(
      method_exists($beforeRedirectEvent, 'getPluginId'),
      'BeforeRedirectEvent class does not implements getPluginId function/method'
    );

    $this->assertTrue(
      method_exists($beforeRedirectEvent, 'getDestination'),
      'BeforeRedirectEvent class does not implements getDestination function/method'
    );

    $this->assertSame('drupal123', $beforeRedirectEvent->getPluginId());
    $this->assertSame('drupal123', $beforeRedirectEvent->getDestination());
    $this->assertEquals($data_handler, $beforeRedirectEvent->getDataHandler());
  }

  /**
   * Tests for class FailedAuthenticationEvent.
   */
  public function testFailedAuthenticationEvent() {

    $error = "error404";
    $pluginId = 'drupal123';
    $data_handler = $this->createMock(SocialAuthDataHandler::class);
    $response = $this->createMock(RedirectResponse::class);

    $failedAuthenticationEvent = $this->getMockBuilder(FailedAuthenticationEvent::class)
      ->setConstructorArgs([$data_handler,
        $pluginId,
        $error,
      ])
      ->setMethods(null)
      ->getMock();

    // parent::__construct($data_handler, $pluginId, $error);.
    $failedAuthenticationEvent->setResponse($response);

    $this->assertTrue(
      method_exists($failedAuthenticationEvent, 'getDataHandler'),
      'FailedAuthenticationEvent class does not implements getDataHandler function/method'
    );

    $this->assertTrue(
        method_exists($failedAuthenticationEvent, 'getPluginId'),
        'FailedAuthenticationEvent class does not implements getPluginId function/method'
      );
    $this->assertTrue(
        method_exists($failedAuthenticationEvent, 'getError'),
        'FailedAuthenticationEvent class does not implements getError function/method'
        );

    $this->assertTrue(
        method_exists($failedAuthenticationEvent, 'getResponse'),
        'FailedAuthenticationEvent class does not implements getResponse function/method'
      );
    $this->assertTrue(
        method_exists($failedAuthenticationEvent, 'setResponse'),
        'FailedAuthenticationEvent class does not implements setResponse function/method'
        );

    $this->assertTrue(
        method_exists($failedAuthenticationEvent, 'hasResponse'),
        'FailedAuthenticationEvent class does not implements hasResponse function/method'
      );

    $this->assertEquals($response, $failedAuthenticationEvent->getResponse());
    $this->assertTrue($failedAuthenticationEvent->hasResponse());
    $this->assertEquals('error404', $failedAuthenticationEvent->getError());
    $this->assertEquals('drupal123', $failedAuthenticationEvent->getPluginId());
    $this->assertEquals(TRUE, $failedAuthenticationEvent->hasResponse());
    $this->assertEquals($data_handler, $failedAuthenticationEvent->getDataHandler());
  }

  /**
   * Tests for class SocialAuthEvents.
   */
  public function testSocialAuthEvents() {
    $reflection = new ReflectionClass(SocialAuthEvents::class);

    $user_fields = $reflection->getConstant('USER_FIELDS');
    $user_created = $reflection->getConstant('USER_CREATED');
    $user_login = $reflection->getConstant('USER_LOGIN');
    $user_redirect = $reflection->getConstant('BEFORE_REDIRECT');
    $faield_auth = $reflection->getConstant('FAILED_AUTH');

    $this->assertEquals('social_auth.user.fields',
          $reflection->getConstant('USER_FIELDS'),
          'The constant values is not matched');

    $this->assertEquals('social_auth.user.created',
          $reflection->getConstant('USER_CREATED'),
          'The constant values is not matched');

    $this->assertEquals('social_auth.user.login',
          $reflection->getConstant('USER_LOGIN'),
          'The constant values is not matched');

    $this->assertEquals('social_auth.before_redirect',
          $reflection->getConstant('BEFORE_REDIRECT'),
          'The constant values is not matched');

    $this->assertEquals('social_auth.failed_authentication',
          $reflection->getConstant('FAILED_AUTH'),
          'The constant values is not matched');
  }

  /**
   * Tests for class UserEvent.
   */
  public function testUserEvent() {
    $pluginId = 'drupal123';
    $user = $this->createMock(UserInterface::class);

    $userEvent = $this->getMockBuilder(UserEvent::class)
      ->setConstructorArgs([$user, $pluginId])
      ->setMethods(null)
      ->getMock();

    $this->assertTrue(
    method_exists($userEvent, 'getPluginId'),
    'UserEvent class does not implements getPluginId function/method'
    );

    $this->assertTrue(
    method_exists($userEvent, 'getUser'),
    'UserEvent class does not implements getUser function/method'
    );

    $this->assertSame('drupal123', $userEvent->getPluginId());
    $this->assertEquals($user, $userEvent->getUser());
  }

  /**
   * Tests for class UserFieldsEvent.
   */
  public function testUserFieldsEvent() {
    // $this->user = $this->createMock(UserInterface::class);.
    $pluginId = 'drupal123';
    $user_fields = ['userfield', 'userfield2'];

    $userFieldsEvent = $this->getMockBuilder(UserFieldsEvent::class)
      ->setConstructorArgs([$user_fields, $pluginId])
      ->setMethods(null)
      ->getMock();

    $userFieldsEvent->setUserFields($user_fields);

    $this->assertTrue(
    method_exists($userFieldsEvent, 'getUserFields'),
    'UserFieldsEvent does not implements getUserFields function/method'
    );

    $this->assertTrue(
    method_exists($userFieldsEvent, 'setUserFields'),
    'UserFieldsEvent does not implements setUserFields function/method'
    );

    $this->assertTrue(
    method_exists($userFieldsEvent, 'getPluginId'),
    'UserFieldsEvent does not implements getPluginId function/method'
    );

    $this->assertSame('drupal123', $userFieldsEvent->getPluginId());
    $this->assertEquals($user_fields, $userFieldsEvent->getUserFields());
  }

}
