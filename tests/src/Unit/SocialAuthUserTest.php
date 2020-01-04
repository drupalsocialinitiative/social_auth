<?php

namespace Drupal\Tests\social_auth\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
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
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Tests social_auth User.
 *
 * @group social_auth
 */
class SocialAuthUserTest extends UnitTestCase {

  /**
   * The tested Social Auth User.
   *
   * @var \Drupal\social_auth\User\SocialAuthUser|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $socialAuthUser;

  /**
   * The tested Social Auth UserManager.
   *
   * @var \Drupal\social_auth\User\UserManager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $userManager;

  /**
   * The tested Social Auth UserAuthenticator.
   *
   * @var \Drupal\social_auth\User\UserAuthenticator
   */
  protected $userAuthenticator;

  /**
   * The mocked AccountProxyInterface.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The mocked LoggerChannelFactoryInterface.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The mocked Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The mocked Data Handler.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  protected $dataHandler;

  /**
   * The mocked Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The mocked Route Provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The mocked Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The test provider user id.
   *
   * @var string
   */
  protected $providerUserId = 'some_id';

  /**
   * The test plugin id.
   *
   * @var string
   */
  protected $pluginId = 'social_auth_test';

  /**
   * {@inheritdoc}
   */
  public function setUp() {

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit_Framework_MockObject_MockObject $container */
    $container = $this->createMock(ContainerInterface::class);
    \Drupal::setContainer($container);

    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);
    $this->currentUser = $this->createMock(AccountProxyInterface::class);
    $this->dataHandler = $this->createMock(SocialAuthDataHandler::class);
    $entity_field_manager = $this->createMock(EntityFieldManagerInterface::class);
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
    $file_system = $this->createMock(FileSystemInterface::class);
    $language_manager = $this->createMock(LanguageManagerInterface::class);
    $this->loggerFactory = $this->createMock(LoggerChannelFactoryInterface::class);
    $this->messenger = $this->createMock(MessengerInterface::class);
    $this->routeProvider = $this->createMock(RouteProviderInterface::class);
    $token = $this->createMock(Token::class);
    $transliteration = $this->createMock(PhpTransliteration::class);

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
        $this->messenger,
        $this->loggerFactory,
        $this->configFactory,
        $entity_field_manager,
        $transliteration,
        $language_manager,
        $this->eventDispatcher,
        $token,
        $file_system,
      ])
      ->setMethods(['addUserRecord',
        'getDrupalUserId',
        'loadUserByProperty',
        'createNewUser',
      ])
      ->getMock();

    $this->userAuthenticator = $this->getMockBuilder(UserAuthenticator::class)
      ->setConstructorArgs([$this->currentUser,
        $this->messenger,
        $this->loggerFactory,
        $this->userManager,
        $this->dataHandler,
        $this->configFactory,
        $this->routeProvider,
        $this->eventDispatcher,
      ])
      ->setMethods(['getLoginFormRedirection',
        'getPostLoginRedirection',
        'userLoginFinalize',
        'isRegistrationDisabled',
        'isApprovalRequired',
        'redirectToUserForm',
        'isAdminDisabled',
        'isUserRoleDisabled',
      ])
      ->getMock();

    $this->currentUser->expects($this->any())
      ->method('id')
      ->will($this->returnValue(12345));
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

  /**
   * Tests the associateNewProvider method with true returned.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::associateNewProvider
   */
  public function testAssociateNewProviderTrue() {
    $redirect = new RedirectResponse('https://drupal.org/');
    $this->userManager->expects($this->any())
      ->method('addUserRecord')
      ->with($this->currentUser->id(), $this->isType('string'), $this->isType('string'), $this->isType('array'))
      ->will($this->returnValue(TRUE));

    $this->userAuthenticator->expects($this->any())
      ->method('getPostLoginRedirection')
      ->will($this->returnValue($redirect));

    $this->userAuthenticator->associateNewProvider('social_auth_test', 'd278127t8', ['test']);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the associateNewProvider method with false returned.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::associateNewProvider
   */
  public function testAssociateNewProviderFalse() {
    $redirect = new RedirectResponse('https://drupal.org/');
    $this->userManager->expects($this->any())
      ->method('addUserRecord')
      ->with($this->currentUser->id(), $this->isType('string'), $this->isType('string'), $this->isType('array'))
      ->will($this->returnValue(FALSE));

    $this->userAuthenticator->expects($this->any())
      ->method('getLoginFormRedirection')
      ->will($this->returnValue($redirect));

    $this->messenger->expects($this->exactly(1))
      ->method('addError');

    $this->userAuthenticator->associateNewProvider('social_auth_test2', 'vdsa2rrf', ['test2']);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the checkProviderIsAssociated method with Drupal user not existing.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::checkProviderIsAssociated
   */
  public function testCheckProviderIsAssociatedNotExist() {
    $this->userManager->expects($this->once())
      ->method('getDrupalUserId')
      ->with($this->providerUserId)
      ->will($this->returnValue(FALSE));

    $this->assertFalse($this->userAuthenticator->checkProviderIsAssociated($this->providerUserId));
  }

  /**
   * Tests the checkProviderIsAssociated method with Drupal user existing.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::checkProviderIsAssociated
   */
  public function testCheckProviderIsAssociatedExist() {
    $this->userManager->expects($this->once())
      ->method('getDrupalUserId')
      ->with($this->providerUserId)
      ->will($this->returnValue(6721234));

    $this->assertEquals(6721234, $this->userAuthenticator->checkProviderIsAssociated($this->providerUserId));
  }

  /**
   * Tests the loginUser method with not active account.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::loginUser
   */
  public function testLoginUserNotActive() {
    $user = $this->createMock(UserInterface::class);
    $logger = $this->createMock(LoggerChannelInterface::class);
    $user->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(FALSE));

    $this->loggerFactory->expects($this->once())
      ->method('get')
      ->with($this->pluginId)
      ->will($this->returnValue($logger));

    $logger->expects($this->once())
      ->method('warning')
      ->with($this->anything());

    $this->userAuthenticator->setPluginId($this->pluginId);
    $this->assertFalse($this->userAuthenticator->loginUser($user));
  }

  /**
   * Tests the loginUser method with active account.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::loginUser
   */
  public function testLoginUserActive() {
    $user = $this->createMock(UserInterface::class);

    $user->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(TRUE));

    $this->userAuthenticator->expects($this->once())
      ->method('userLoginFinalize')
      ->with($user);

    $this->userAuthenticator->setPluginId($this->pluginId);
    $this->assertTrue($this->userAuthenticator->loginUser($user));
  }

  /**
   * Tests the authenticateWithProvider method with successful authentication.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateWithProvider
   */
  public function testAuthenticateWithProviderSuccess() {
    $this->prepareAuthenticateWithProvider();
    $user = $this->createMock(User::class);

    $this->userManager->expects($this->once())
      ->method('loadUserByProperty')
      ->with('uid', 12345)
      ->will($this->returnValue($user));

    $this->userAuthenticator->expects($this->once())
      ->method('authenticateExistingUser')
      ->with($user);

    $this->assertTrue($this->userAuthenticator->authenticateWithProvider(12345));

  }

  /**
   * Tests the authenticateWithProvider method with failure while loading user.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateWithProvider
   */
  public function testAuthenticateWithProviderFailLoading() {
    $this->userManager->expects($this->once())
      ->method('loadUserByProperty')
      ->with('uid', 12345)
      ->will($this->returnValue(NULL));

    $this->assertFalse($this->userAuthenticator->authenticateWithProvider(12345));
  }

  /**
   * Tests the authenticateWithProvider method with exception thrown.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateWithProvider
   */
  public function testAuthenticateWithProviderException() {
    $logger = $this->createMock(LoggerChannelInterface::class);
    $this->userManager->expects($this->once())
      ->method('loadUserByProperty')
      ->with('uid', 12345)
      ->will($this->returnCallback(function () {
               throw new \Exception('test');
      }));

    $this->loggerFactory->expects($this->once())
      ->method('get')
      ->with($this->pluginId)
      ->will($this->returnValue($logger));

    $logger->expects($this->once())
      ->method('error')
      ->with($this->anything());

    $this->userAuthenticator->setPluginId($this->pluginId);
    $this->assertFalse($this->userAuthenticator->authenticateWithProvider(12345));

  }

  /**
   * Tests the authenticateNewUser method with not valid user.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateNewUser
   */
  public function testAuthenticateNewUserNotValid() {
    $this->prepareAuthenticateNewUser();
    $redirect = new RedirectResponse('https://drupal.org/');

    $this->userAuthenticator->expects($this->once())
      ->method('isRegistrationDisabled')
      ->will($this->returnValue(TRUE));

    $this->userAuthenticator->expects($this->any())
      ->method('getLoginFormRedirection')
      ->will($this->returnValue($redirect));

    $this->userAuthenticator->authenticateNewUser(NULL);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the authenticateNewUser method valid user and need admin approval.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateNewUser
   */
  public function testAuthenticateNewUserValidApproveNeed() {
    $this->prepareAuthenticateNewUser();
    $redirect = new RedirectResponse('https://drupal.org/');
    $user = $this->createMock(UserInterface::class);

    $this->userAuthenticator->expects($this->once())
      ->method('isApprovalRequired')
      ->will($this->returnValue(TRUE));

    $this->userAuthenticator->expects($this->any())
      ->method('getLoginFormRedirection')
      ->will($this->returnValue($redirect));

    $this->messenger->expects($this->exactly(1))
      ->method('addWarning');

    $this->userAuthenticator->authenticateNewUser($user);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the authenticateNewUser method with valid user and fail logging in.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateNewUser
   */
  public function testAuthenticateNewUserValidFailLogin() {
    $this->prepareAuthenticateNewUser();

    $redirect = new RedirectResponse('https://drupal.org/');
    $user = $this->createMock(UserInterface::class);

    $this->userAuthenticator->expects($this->once())
      ->method('isApprovalRequired')
      ->will($this->returnValue(FALSE));

    $this->userAuthenticator->expects($this->once())
      ->method('loginUser')
      ->with($user)
      ->will($this->returnValue(FALSE));

    $this->userAuthenticator->expects($this->once())
      ->method('isRegistrationDisabled')
      ->will($this->returnValue(TRUE));

    $this->userAuthenticator->expects($this->any())
      ->method('getLoginFormRedirection')
      ->will($this->returnValue($redirect));

    $this->userAuthenticator->setPluginId($this->pluginId);
    $this->userAuthenticator->authenticateNewUser($user);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the authenticateNewUser method login and redirect to login form.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateNewUser
   */
  public function testAuthenticateNewUserValidLoginFormRedirect() {
    $this->prepareAuthenticateNewUser();
    $redirect = new RedirectResponse('https://drupal.org/');
    $user = $this->createMock(UserInterface::class);

    $this->userAuthenticator->expects($this->once())
      ->method('isApprovalRequired')
      ->will($this->returnValue(FALSE));

    $this->userAuthenticator->expects($this->once())
      ->method('loginUser')
      ->with($user)
      ->will($this->returnValue(TRUE));

    $this->userAuthenticator->expects($this->any())
      ->method('redirectToUserForm')
      ->with($user)
      ->will($this->returnValue($redirect));

    $this->userAuthenticator->setPluginId($this->pluginId);
    $this->userAuthenticator->authenticateNewUser($user);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the authenticateNewUser method login and redirect to post login.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateNewUser
   */
  public function testAuthenticateNewUserValidLoginPostRedirect() {
    $this->prepareAuthenticateNewUser();
    $redirect = new RedirectResponse('https://drupal.org/');
    $user = $this->createMock(UserInterface::class);

    $this->userAuthenticator->expects($this->once())
      ->method('isApprovalRequired')
      ->will($this->returnValue(FALSE));

    $this->userAuthenticator->expects($this->once())
      ->method('loginUser')
      ->with($user)
      ->will($this->returnValue(TRUE));

    $this->userAuthenticator->expects($this->any())
      ->method('redirectToUserForm')
      ->with($user)
      ->will($this->returnValue(NULL));

    $this->userAuthenticator->expects($this->any())
      ->method('getPostLoginRedirection')
      ->will($this->returnValue($redirect));

    $this->userAuthenticator->setPluginId($this->pluginId);
    $this->userAuthenticator->authenticateNewUser($user);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the authenticateWithEmail method with account existing.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateWithEmail
   */
  public function testAuthenticateWithEmailAccountExist() {
    $this->prepareAuthenticateWithEmailAccountExist();
    $user = $this->createMock(User::class);
    $this->userManager->expects($this->once())
      ->method('loadUserByProperty')
      ->with('mail', 'test@gmail.com')
      ->will($this->returnValue($user));

    $this->userManager->expects($this->once())
      ->method('addUserRecord')
      ->with($this->anything());

    $this->userAuthenticator->expects($this->once())
      ->method('authenticateExistingUser')
      ->with($this->anything());

    $this->assertTrue($this->userAuthenticator->authenticateWithEmail('test@gmail.com', $this->providerUserId, '2873dgAS', ['test']));
  }

  /**
   * Tests the authenticateWithEmail method with account not existing.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateWithEmail
   */
  public function testAuthenticateWithEmailAccountNoExist() {
    $this->prepareAuthenticateWithEmailAccountExist();
    $logger = $this->createMock(LoggerChannelInterface::class);
    $this->userManager->expects($this->once())
      ->method('loadUserByProperty')
      ->with('mail', 'test@gmail.com')
      ->will($this->returnCallback(function () {
               throw new \Exception('test');
      }));

    $this->loggerFactory->expects($this->once())
      ->method('get')
      ->with($this->pluginId)
      ->will($this->returnValue($logger));

    $logger->expects($this->once())
      ->method('error')
      ->with($this->anything());

    $this->userAuthenticator->setPluginId($this->pluginId);
    $this->assertFalse($this->userAuthenticator->authenticateWithEmail('test@gmail.com', $this->providerUserId, '2873dgAS', ['test']));
  }

  /**
   * Tests the authenticateExistingUser method with admin auth disabled.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateExistingUser
   */
  public function testAuthenticateExistingUserAdminDisabled() {
    $this->prepareAuthenticateExistingUser();
    $user = $this->createMock(UserInterface::class);
    $redirect = new RedirectResponse('https://drupal.org/');

    $this->userAuthenticator->expects($this->once())
      ->method('isAdminDisabled')
      ->with($user)
      ->will($this->returnValue(TRUE));

    $this->messenger->expects($this->exactly(1))
      ->method('addError');

    $this->userAuthenticator->expects($this->any())
      ->method('getLoginFormRedirection')
      ->will($this->returnValue($redirect));

    $this->userAuthenticator->authenticateExistingUser($user);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the authenticateExistingUser method with user role auth disabled.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateExistingUser
   */
  public function testAuthenticateExistingUserRoleDisabled() {
    $this->prepareAuthenticateExistingUser();
    $user = $this->createMock(UserInterface::class);
    $redirect = new RedirectResponse('https://drupal.org/');

    $this->userAuthenticator->expects($this->once())
      ->method('isAdminDisabled')
      ->with($user)
      ->will($this->returnValue(FALSE));

    $this->userAuthenticator->expects($this->once())
      ->method('isUserRoleDisabled')
      ->with($user)
      ->will($this->returnValue(TRUE));

    $this->messenger->expects($this->exactly(1))
      ->method('addError');

    $this->userAuthenticator->expects($this->any())
      ->method('getLoginFormRedirection')
      ->will($this->returnValue($redirect));

    $this->userAuthenticator->authenticateExistingUser($user);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the authenticateExistingUser method with successful logging in.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateExistingUser
   */
  public function testAuthenticateExistingUserSucessLogin() {
    $this->prepareAuthenticateExistingUser();
    $user = $this->createMock(UserInterface::class);
    $redirect = new RedirectResponse('https://drupal.org/');

    $this->userAuthenticator->expects($this->once())
      ->method('isAdminDisabled')
      ->with($user)
      ->will($this->returnValue(FALSE));

    $this->userAuthenticator->expects($this->once())
      ->method('isUserRoleDisabled')
      ->with($user)
      ->will($this->returnValue(FALSE));

    $this->userAuthenticator->expects($this->once())
      ->method('loginUser')
      ->with($user)
      ->will($this->returnValue(TRUE));

    $this->userAuthenticator->expects($this->any())
      ->method('getPostLoginRedirection')
      ->will($this->returnValue($redirect));

    $this->userAuthenticator->setPluginId($this->pluginId);
    $this->userAuthenticator->authenticateExistingUser($user);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the authenticateExistingUser method with failure logging in.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateExistingUser
   */
  public function testAuthenticateExistingUserFailureLogin() {
    $this->prepareAuthenticateExistingUser();
    $user = $this->createMock(UserInterface::class);
    $redirect = new RedirectResponse('https://drupal.org/');

    $this->userAuthenticator->expects($this->once())
      ->method('isAdminDisabled')
      ->with($user)
      ->will($this->returnValue(FALSE));

    $this->userAuthenticator->expects($this->once())
      ->method('isUserRoleDisabled')
      ->with($user)
      ->will($this->returnValue(FALSE));

    $this->userAuthenticator->expects($this->once())
      ->method('loginUser')
      ->will($this->returnValue(FALSE));

    $this->messenger->expects($this->exactly(1))
      ->method('addError');

    $this->userAuthenticator->expects($this->any())
      ->method('getLoginFormRedirection')
      ->will($this->returnValue($redirect));

    $this->userAuthenticator->setPluginId($this->pluginId);
    $this->userAuthenticator->authenticateExistingUser($user);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the authenticateUser method - no record for provider exists.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateUser
   */
  public function testAuthenticateUserNoRecord() {
    $this->prepareAuthenticateUser();
    $redirect = new RedirectResponse('https://drupal.org/');
    $this->userManager->expects($this->once())
      ->method('getDrupalUserId')
      ->with($this->providerUserId)
      ->will($this->returnValue(FALSE));

    $this->currentUser->expects($this->once())
      ->method('isAuthenticated')
      ->will($this->returnValue(TRUE));

    $this->userAuthenticator->expects($this->any())
      ->method('associateNewProvider')
      ->with($this->anything())
      ->will($this->returnCallback(function () {
        $redirect = new RedirectResponse('https://drupal.org/');
        $this->userAuthenticator->setResponse($redirect);
      }));

    $this->userAuthenticator->authenticateUser('username', 'test@gmail.com', $this->providerUserId, 'S92xzuwssa2', 'https://www.drupal.org/files/styles/grid-2/public/default-avatar.png', ['data']);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the authenticateUser method - provider is associated.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateUser
   */
  public function testAuthenticateUserProviderAssociated() {
    $this->prepareAuthenticateUser();
    $redirect = new RedirectResponse('https://drupal.org/');
    $this->userManager->expects($this->once())
      ->method('getDrupalUserId')
      ->with($this->providerUserId)
      ->will($this->returnValue(123456));

    $this->currentUser->expects($this->once())
      ->method('isAuthenticated')
      ->will($this->returnValue(TRUE));

    $this->userAuthenticator->expects($this->any())
      ->method('getPostLoginRedirection')
      ->will($this->returnValue($redirect));

    $this->assertEquals($redirect, $this->userAuthenticator->authenticateUser('username', 'test@gmail.com', $this->providerUserId, 'S92xzuwssa2', 'https://www.drupal.org/files/styles/grid-2/public/default-avatar.png', ['data']));
  }

  /**
   * Tests the authenticateUser method - auth with provider.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateUser
   */
  public function testAuthenticateUserAuthProvider() {
    $this->prepareAuthenticateUser();
    $redirect = new RedirectResponse('https://drupal.org/');

    $this->userManager->expects($this->once())
      ->method('getDrupalUserId')
      ->with($this->providerUserId)
      ->will($this->returnValue(123456));

    $this->currentUser->expects($this->once())
      ->method('isAuthenticated')
      ->will($this->returnValue(FALSE));

    $this->userAuthenticator->expects($this->once())
      ->method('authenticateWithProvider')
      ->with(123456)
      ->will($this->returnCallback(function () {
        $redirect = new RedirectResponse('https://drupal.org/');
        $this->userAuthenticator->setResponse($redirect);
      }));

    $this->userAuthenticator->authenticateUser('username', 'test@gmail.com', $this->providerUserId, 'S92xzuwssa2', 'https://www.drupal.org/files/styles/grid-2/public/default-avatar.png', ['data']);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the authenticateUser method - auth with email.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateUser
   */
  public function testAuthenticateUserAuthEmail() {
    $this->prepareAuthenticateUser();
    $redirect = new RedirectResponse('https://drupal.org/');

    $this->userManager->expects($this->once())
      ->method('getDrupalUserId')
      ->with($this->providerUserId)
      ->will($this->returnValue(NULL));

    $this->currentUser->expects($this->once())
      ->method('isAuthenticated')
      ->will($this->returnValue(FALSE));

    $this->userAuthenticator->expects($this->once())
      ->method('authenticateWithEmail')
      ->with($this->anything())
      ->will($this->returnCallback(function () {
        $redirect = new RedirectResponse('https://drupal.org/');
        $this->userAuthenticator->setResponse($redirect);
      }));

    $this->userAuthenticator->authenticateUser('username', 'test@gmail.com', $this->providerUserId, 'S92xzuwssa2', 'https://www.drupal.org/files/styles/grid-2/public/default-avatar.png', ['data']);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * Tests the authenticateUser method - create new user.
   *
   * @covers Drupal\social_auth\User\UserAuthenticator::authenticateUser
   */
  public function testAuthenticateUserNewUser() {
    $this->prepareAuthenticateUser();
    $user = $this->createMock(UserInterface::class);
    $redirect = new RedirectResponse('https://drupal.org/');

    $this->userManager->expects($this->once())
      ->method('getDrupalUserId')
      ->with($this->providerUserId)
      ->will($this->returnValue(NULL));

    $this->currentUser->expects($this->once())
      ->method('isAuthenticated')
      ->will($this->returnValue(FALSE));

    $this->userManager->expects($this->once())
      ->method('createNewUser')
      ->with($this->anything())
      ->will($this->returnValue($user));

    $this->userAuthenticator->expects($this->once())
      ->method('authenticateNewUser')
      ->with($user)
      ->will($this->returnCallback(function () {
        $redirect = new RedirectResponse('https://drupal.org/');
        $this->userAuthenticator->setResponse($redirect);
      }));

    $this->userAuthenticator->setPluginId($this->pluginId);
    $this->userAuthenticator->authenticateUser('username', '', $this->providerUserId, 'S92xzuwssa2', 'https://www.drupal.org/files/styles/grid-2/public/default-avatar.png', ['data']);
    $this->assertEquals($redirect, $this->userAuthenticator->getResponse());
  }

  /**
   * UserAuthenticator with mocked methods for authenticateWithProvider tests.
   */
  protected function prepareAuthenticateWithProvider() {
    unset($this->userAuthenticator);
    $this->userAuthenticator = $this->getMockBuilder(UserAuthenticator::class)
      ->setConstructorArgs([$this->currentUser,
        $this->messenger,
        $this->loggerFactory,
        $this->userManager,
        $this->dataHandler,
        $this->configFactory,
        $this->routeProvider,
        $this->eventDispatcher,
      ])
      ->setMethods(['authenticateExistingUser'])
      ->getMock();
  }

  /**
   * UserAuthenticator with mocked methods for authenticateNewUser tests.
   */
  protected function prepareAuthenticateNewUser() {
    unset($this->userAuthenticator);
    $this->userAuthenticator = $this->getMockBuilder(UserAuthenticator::class)
      ->setConstructorArgs([$this->currentUser,
        $this->messenger,
        $this->loggerFactory,
        $this->userManager,
        $this->dataHandler,
        $this->configFactory,
        $this->routeProvider,
        $this->eventDispatcher,
      ])
      ->setMethods(['loginUser',
        'isRegistrationDisabled',
        'isApprovalRequired',
        'getLoginFormRedirection',
        'redirectToUserForm',
        'getPostLoginRedirection',
      ])
      ->getMock();
  }

  /**
   * UserAuthenticator with mocked methods for authenticateWithEmail tests.
   */
  protected function prepareAuthenticateWithEmailAccountExist() {
    unset($this->userAuthenticator);
    $this->userAuthenticator = $this->getMockBuilder(UserAuthenticator::class)
      ->setConstructorArgs([$this->currentUser,
        $this->messenger,
        $this->loggerFactory,
        $this->userManager,
        $this->dataHandler,
        $this->configFactory,
        $this->routeProvider,
        $this->eventDispatcher,
      ])
      ->setMethods(['addUserRecord',
        'authenticateExistingUser',
      ])
      ->getMock();
  }

  /**
   * UserAuthenticator with mocked methods for authenticateExistingUser tests.
   */
  protected function prepareAuthenticateExistingUser() {
    unset($this->userAuthenticator);
    $this->userAuthenticator = $this->getMockBuilder(UserAuthenticator::class)
      ->setConstructorArgs([$this->currentUser,
        $this->messenger,
        $this->loggerFactory,
        $this->userManager,
        $this->dataHandler,
        $this->configFactory,
        $this->routeProvider,
        $this->eventDispatcher,
      ])
      ->setMethods(['isAdminDisabled',
        'getLoginFormRedirection',
        'isUserRoleDisabled',
        'loginUser',
        'getPostLoginRedirection',
      ])
      ->getMock();
  }

  /**
   * UserAuthenticator with mocked methods for authenticateUser tests.
   */
  protected function prepareAuthenticateUser() {
    unset($this->userAuthenticator);
    $this->userAuthenticator = $this->getMockBuilder(UserAuthenticator::class)
      ->setConstructorArgs([$this->currentUser,
        $this->messenger,
        $this->loggerFactory,
        $this->userManager,
        $this->dataHandler,
        $this->configFactory,
        $this->routeProvider,
        $this->eventDispatcher,
      ])
      ->setMethods(['associateNewProvider',
        'getPostLoginRedirection',
        'authenticateWithProvider',
        'authenticateWithEmail',
        'authenticateNewUser',
      ])
      ->getMock();
  }

}
