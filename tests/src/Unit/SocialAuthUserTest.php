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

    $name = 'agrochal';
    $email = 'test@gmail.com';
    $provider_user_id = 3141592;
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
   * Tests for class SocialAuthUser.
   */
  public function testSocialAuthUser() {

    $this->assertTrue(
      method_exists($this->socialAuthUser, 'getFirstName'),
      'SocialAuthUser class does not implements getFirstName function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'setFirstName'),
      'SocialAuthUser class does not implements setFirstName function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'getLastName'),
      'SocialAuthUser class does not implements getLastName function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'setLastName'),
      'SocialAuthUser class does not implements setLastName function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'getName'),
      'SocialAuthUser class does not implements getName function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'setName'),
      'SocialAuthUser class does not implements setName function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'getEmail'),
      'SocialAuthUser class does not implements getEmail function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'setEmail'),
      'SocialAuthUser class does not implements setEmail function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'getProviderId'),
      'SocialAuthUser class does not implements getProviderId function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'setProviderId'),
      'SocialAuthUser class does not implements setProviderId function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'getToken'),
      'SocialAuthUser class does not implements getToken function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'setToken'),
      'SocialAuthUser class does not implements setToken function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'getPictureUrl'),
      'SocialAuthUser class does not implements getPictureUrl function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'setPictureUrl'),
      'SocialAuthUser class does not implements setPictureUrl function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'getPicture'),
      'SocialAuthUser class does not implements getPicture function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'setPicture'),
      'SocialAuthUser class does not implements setPicture function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'getAdditionalData'),
      'SocialAuthUser class does not implements getAdditionalData function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'setAdditionalData'),
      'SocialAuthUser class does not implements setAdditionalData function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'addData'),
      'SocialAuthUser class does not implements addData function/method'
    );
    $this->assertTrue(
      method_exists($this->socialAuthUser, 'getData'),
      'SocialAuthUser class does not implements getData function/method'
    );

  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setFirstName
   */
  public function testSetFirstName() {
    $this->socialAuthUser->setFirstName('John');
    $this->assertEquals('John', $this->socialAuthUser->getFirstName());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getFirstName
   */
  public function testGetFirstName() {
    $this->socialAuthUser->setFirstName('John');
    $this->assertNotEquals('Arthur', $this->socialAuthUser->getFirstName());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setLastName
   */
  public function testSetLastName() {
    $this->socialAuthUser->setLastName('Doe');
    $this->assertEquals('Doe', $this->socialAuthUser->getLastName());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getLastName
   */
  public function testGetLastName() {
    $this->socialAuthUser->setLastName('Doe');
    $this->assertNotEquals('Smith', $this->socialAuthUser->getLastName());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setName
   */
  public function testSetName() {
    $this->assertEquals('agrochal', $this->socialAuthUser->getName());
    $this->socialAuthUser->setName('somename');
    $this->assertEquals('somename', $this->socialAuthUser->getName());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getName
   */
  public function testGetName() {
    $this->socialAuthUser->setName('agrochal');
    $this->assertNotEquals('helloworld', $this->socialAuthUser->getName());
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
    $this->socialAuthUser->setEmail('true@gmail.com');
    $this->assertNotEquals('false@gmail.com', $this->socialAuthUser->getEmail());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setProviderId
   */
  public function testSetProviderId() {
    $this->assertEquals(3141592, $this->socialAuthUser->getProviderId());
    $this->socialAuthUser->setProviderId(16180339);
    $this->assertEquals(16180339, $this->socialAuthUser->getProviderId());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getProviderId
   */
  public function testGetProviderId() {
    $this->socialAuthUser->setProviderId(16180339);
    $this->assertNotEquals(3141592, $this->socialAuthUser->getProviderId());
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
    $this->assertNotEquals('ndau21as812t17ajk', $this->socialAuthUser->getToken());
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
    $this->assertNotEquals('https://www.drupal.org/files/styles/grid-2/public/extra-avatar.png', $this->socialAuthUser->getPictureUrl());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::setPicture
   */
  public function testSetPicture() {
    $this->socialAuthUser->setPicture('fileid123');
    $this->assertEquals('fileid123', $this->socialAuthUser->getPicture());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getPicture
   */
  public function testGetPicture() {
    $this->socialAuthUser->setPicture('fileid123');
    $this->assertNotEquals('filebadid123', $this->socialAuthUser->getPicture());
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
    $this->socialAuthUser->setAdditionalData('{"id": 1246534534}');
    $this->assertNotEquals('{"id": 63333212}', $this->socialAuthUser->getAdditionalData());
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::addData
   */
  public function testAddData() {
    $this->socialAuthUser->addData('value', 'Information');
    $this->assertEquals('Information', $this->socialAuthUser->getData('value'));
  }

  /**
   * @covers Drupal\social_auth\User\SocialAuthUser::getData
   */
  public function testGetData() {
    $this->socialAuthUser->addData('value', 'Information');
    $this->assertNotEquals('SomeData', $this->socialAuthUser->getData('value'));
  }

  /**
   * Tests for class UserManager.
   */
  public function testUserManager() {
    $this->assertTrue(
      method_exists($this->userManager, 'createNewUser'),
      'UserManager class does not implements createNewUser function/method'
    );
    $this->assertTrue(
      method_exists($this->userManager, 'createUser'),
      'UserManager class does not implements createUser function/method'
    );
    $this->assertTrue(
      method_exists($this->userManager, 'addUserRecord'),
      'UserManager class does not implements addUserRecord function/method'
    );
    $this->assertTrue(
      method_exists($this->userManager, 'loadUserByProperty'),
      'UserManager class does not implements loadUserByProperty function/method'
    );
    $this->assertTrue(
      method_exists($this->userManager, 'saveUser'),
      'UserManager class does not implements saveUser function/method'
    );
    $this->assertTrue(
      method_exists($this->userManager, 'setProfilePic'),
      'UserManager class does not implements setProfilePic function/method'
    );
    $this->assertTrue(
      method_exists($this->userManager, 'downloadProfilePic'),
      'UserManager class does not implements downloadProfilePic function/method'
    );
    $this->assertTrue(
      method_exists($this->userManager, 'generateUniqueUsername'),
      'UserManager class does not implements generateUniqueUsername function/method'
    );
    $this->assertTrue(
      method_exists($this->userManager, 'getUserFields'),
      'UserManager class does not implements getUserFields function/method'
    );
    $this->assertTrue(
      method_exists($this->userManager, 'userPictureEnabled'),
      'UserManager class does not implements userPictureEnabled function/method'
    );
    $this->assertTrue(
      method_exists($this->userManager, 'getPictureDirectory'),
      'UserManager class does not implements getPictureDirectory function/method'
    );
    $this->assertTrue(
      method_exists($this->userManager, 'userPassword'),
      'UserManager class does not implements userPassword function/method'
    );
    $this->assertTrue(
      method_exists($this->userManager, 'systemRetrieveFile'),
      'UserManager class does not implements systemRetrieveFile function/method'
    );

  }

  /**
   * Tests for class UserAuthenticator.
   */
  public function testUserAuthenticator() {
    $this->assertTrue(
      method_exists($this->userAuthenticator, 'setDestination'),
      'UserAuthenticator class does not implements setDestination function/method'
    );
    $this->assertTrue(
      method_exists($this->userAuthenticator, 'authenticateUser'),
      'UserAuthenticator class does not implements authenticateUser function/method'
    );
    $this->assertTrue(
      method_exists($this->userAuthenticator, 'associateNewProvider'),
      'UserAuthenticator class does not implements associateNewProvider function/method'
    );
    $this->assertTrue(
      method_exists($this->userAuthenticator, 'authenticateWithProvider'),
      'UserAuthenticator class does not implements authenticateWithProvider function/method'
    );
    $this->assertTrue(
      method_exists($this->userAuthenticator, 'authenticateWithEmail'),
      'UserAuthenticator class does not implements authenticateWithEmail function/method'
    );
    $this->assertTrue(
      method_exists($this->userAuthenticator, 'authenticateExistingUser'),
      'UserAuthenticator class does not implements authenticateExistingUser function/method'
    );
    $this->assertTrue(
      method_exists($this->userAuthenticator, 'authenticateNewUser'),
      'UserAuthenticator class does not implements authenticateNewUser function/method'
    );
    $this->assertTrue(
      method_exists($this->userAuthenticator, 'loginUser'),
      'UserAuthenticator class does not implements loginUser function/method'
    );
    $this->assertTrue(
      method_exists($this->userAuthenticator, 'checkProviderIsAssociated'),
      'UserAuthenticator class does not implements checkProviderIsAssociated function/method'
    );
    $this->assertTrue(
      method_exists($this->userAuthenticator, 'getLoginFormRedirection'),
      'UserAuthenticator class does not implements getLoginFormRedirection function/method'
    );
    $this->assertTrue(
      method_exists($this->userAuthenticator, 'userLoginFinalize'),
      'UserAuthenticator class does not implements userLoginFinalize function/method'
    );
    $this->assertTrue(
      method_exists($this->userAuthenticator, 'dispatchAuthenticationError'),
      'UserAuthenticator class does not implements dispatchAuthenticationError function/method'
    );
    $this->assertTrue(
      method_exists($this->userAuthenticator, 'dispatchBeforeRedirect'),
      'UserAuthenticator class does not implements dispatchBeforeRedirect function/method'
    );
  }

}
