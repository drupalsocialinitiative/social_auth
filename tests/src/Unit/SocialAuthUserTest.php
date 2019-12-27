<?php

namespace Drupal\Tests\social_auth\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Transliteration\PhpTransliteration;
use Drupal\Core\Utility\Token;
use Drupal\social_auth\SocialAuthDataHandler;
use Drupal\social_auth\User\SocialAuthUser;
use Drupal\social_auth\User\UserAuthenticator;
use Drupal\social_auth\User\UserManager;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Tests social_auth User.
 *
 * @group social_auth
 */
class SocialAuthUserTest extends UnitTestCase {

  /**
   * The tested Social Auth User.
   *
   * @var \Drupal\social_auth\User\SocialAuthUser
   */
  protected $socialAuthUser;

  /**
   * The tested Social Auth UserManager.
   *
   * @var \Drupal\social_auth\User\UserManager
   */
  protected $userManager;

  /**
   * The tested Social Auth UserAuthenticator.
   *
   * @var \Drupal\social_auth\User\UserAuthenticator
   */
  protected $userAuthenticator;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $config_factory = $this->createMock(ConfigFactoryInterface::class);
    $current_user = $this->createMock(AccountProxyInterface::class);
    $data_handler = $this->createMock(SocialAuthDataHandler::class);
    $entity_field_manager = $this->createMock(EntityFieldManagerInterface::class);
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $event_dispatcher = $this->createMock(EventDispatcherInterface::class);
    $file_system = $this->createMock(FileSystemInterface::class);
    $language_manager = $this->createMock(LanguageManagerInterface::class);
    $logger_factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $messenger = $this->createMock(MessengerInterface::class);
    $route_provider = $this->createMock(RouteProviderInterface::class);
    $token = $this->createMock(Token::class);
    $transliteration = $this->createMock(PhpTransliteration::class);
    $user_manager = $this->createMock(UserManager::class);

    $name = 'test';
    $email = 'test@gmail.com';
    $provider_user_id = 'some_id';
    $token_auth = '712gd21asda862fa2da2da';
    $picture_url = 'https://www.drupal.org/files/styles/grid-2/public/my-avatar.png';
    $additional_data = '{"age": 22}';

    $this->socialAuthUser = $this->getMockBuilder(SocialAuthUser::class)
      ->setConstructorArgs([$name,
        $email,
        $provider_user_id,
        $token_auth,
        $picture_url,
        $additional_data,
      ])
      ->setMethods(NULL)
      ->getMock();

    $this->userManager = $this->getMockBuilder(UserManager::class)
      ->setConstructorArgs([$entity_type_manager,
        $messenger,
        $logger_factory,
        $config_factory,
        $entity_field_manager,
        $transliteration,
        $language_manager,
        $event_dispatcher,
        $token,
        $file_system,
      ])
      ->setMethods(NULL)
      ->getMock();

    $this->userAuthenticator = $this->getMockBuilder(UserAuthenticator::class)
      ->setConstructorArgs([$current_user,
        $messenger,
        $logger_factory,
        $user_manager,
        $data_handler,
        $config_factory,
        $route_provider,
        $event_dispatcher,
      ])
      ->setMethods(NULL)
      ->getMock();
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setFirstName
   */
  public function testSetFirstName() {
    $this->assertNotEquals('John', $this->socialAuthUser->getFirstName());
    $this->socialAuthUser->setFirstName('John');
    $this->assertEquals('John', $this->socialAuthUser->getFirstName());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getFirstName
   */
  public function testGetFirstName() {
    $this->socialAuthUser->setFirstName('Arthur');
    $this->assertEquals('Arthur', $this->socialAuthUser->getFirstName());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setLastName
   */
  public function testSetLastName() {
    $this->assertNotEquals('Doe', $this->socialAuthUser->getLastName());
    $this->socialAuthUser->setLastName('Doe');
    $this->assertEquals('Doe', $this->socialAuthUser->getLastName());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getLastName
   */
  public function testGetLastName() {
    $this->socialAuthUser->setLastName('Smith');
    $this->assertEquals('Smith', $this->socialAuthUser->getLastName());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setName
   */
  public function testSetName() {
    $this->assertEquals('test', $this->socialAuthUser->getName());
    $this->socialAuthUser->setName('somename');
    $this->assertEquals('somename', $this->socialAuthUser->getName());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getName
   */
  public function testGetName() {
    $this->socialAuthUser->setName('test2');
    $this->assertEquals('test2', $this->socialAuthUser->getName());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setEmail
   */
  public function testSetEmail() {
    $this->assertEquals('test@gmail.com', $this->socialAuthUser->getEmail());
    $this->socialAuthUser->setEmail('true@gmail.com');
    $this->assertEquals('true@gmail.com', $this->socialAuthUser->getEmail());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getEmail
   */
  public function testGetEmail() {
    $this->socialAuthUser->setEmail('somemail@gmail.com');
    $this->assertEquals('somemail@gmail.com', $this->socialAuthUser->getEmail());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setProviderId
   */
  public function testSetProviderId() {
    $this->assertEquals('some_id', $this->socialAuthUser->getProviderId());
    $this->socialAuthUser->setProviderId('another_id');
    $this->assertEquals('another_id', $this->socialAuthUser->getProviderId());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getProviderId
   */
  public function testGetProviderId() {
    $this->socialAuthUser->setProviderId('provider_id');
    $this->assertEquals('provider_id', $this->socialAuthUser->getProviderId());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setToken
   */
  public function testSetToken() {
    $this->assertEquals('712gd21asda862fa2da2da', $this->socialAuthUser->getToken());
    $this->socialAuthUser->setToken('21h8dazsa092b');
    $this->assertEquals('21h8dazsa092b', $this->socialAuthUser->getToken());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getToken
   */
  public function testGetToken() {
    $this->socialAuthUser->setToken('21h8dazsa092b');
    $this->assertEquals('21h8dazsa092b', $this->socialAuthUser->getToken());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setPictureUrl
   */
  public function testSetPictureUrl() {
    $this->assertEquals('https://www.drupal.org/files/styles/grid-2/public/my-avatar.png', $this->socialAuthUser->getPictureUrl());
    $this->socialAuthUser->setPictureUrl('https://www.drupal.org/files/styles/grid-2/public/default-avatar.png');
    $this->assertEquals('https://www.drupal.org/files/styles/grid-2/public/default-avatar.png', $this->socialAuthUser->getPictureUrl());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getPictureUrl
   */
  public function testGetPictureUrl() {
    $this->socialAuthUser->setPictureUrl('https://www.drupal.org/files/styles/grid-2/public/default-avatar.png');
    $this->assertEquals('https://www.drupal.org/files/styles/grid-2/public/default-avatar.png', $this->socialAuthUser->getPictureUrl());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setPicture
   */
  public function testSetPicture() {
    $this->assertNotEquals('fileid123', $this->socialAuthUser->getPicture());
    $this->socialAuthUser->setPicture('fileid123');
    $this->assertEquals('fileid123', $this->socialAuthUser->getPicture());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getPicture
   */
  public function testGetPicture() {
    $this->socialAuthUser->setPicture('fileid9899');
    $this->assertEquals('fileid9899', $this->socialAuthUser->getPicture());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setAdditionalData
   */
  public function testSetAdditionalData() {
    $this->assertEquals('{"age": 22}', $this->socialAuthUser->getAdditionalData());
    $this->socialAuthUser->setAdditionalData('{"id": 1246534534}');
    $this->assertEquals('{"id": 1246534534}', $this->socialAuthUser->getAdditionalData());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getAdditionalData
   */
  public function testGetAdditionalData() {
    $this->socialAuthUser->setAdditionalData('{"id": 9876}');
    $this->assertEquals('{"id": 9876}', $this->socialAuthUser->getAdditionalData());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::addData
   */
  public function testAddData() {
    $this->assertNotEquals('Information', $this->socialAuthUser->getData('value'));
    $this->socialAuthUser->addData('value', 'Information');
    $this->assertEquals('Information', $this->socialAuthUser->getData('value'));
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getData
   */
  public function testGetData() {
    $this->socialAuthUser->addData('value2', 'AnotherInformation');
    $this->assertEquals('AnotherInformation', $this->socialAuthUser->getData('value2'));
  }

}
