<?php

/**
 * File containing the RepositoryAuthenticationProviderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\Authentication;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\Security\Authentication\RepositoryAuthenticationProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use eZ\Publish\Core\MVC\Symfony\Security\User;

class RepositoryAuthenticationProviderTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface */
    private $encoderFactory;

    /** @var RepositoryAuthenticationProvider */
    private $authProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\API\Repository\Repository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->encoderFactory = $this->createMock(EncoderFactoryInterface::class);
        $repository = $this->repository = $this->createMock(Repository::class);
        $this->authProvider = new RepositoryAuthenticationProvider(
            $this->createMock(UserProviderInterface::class),
            $this->createMock(UserCheckerInterface::class),
            'foo',
            $this->encoderFactory
        );
        $this->authProvider->setRepository($repository);
    }

    public function testAuthenticationNotEzUser()
    {
        $password = 'some_encoded_password';
        $user = $this->createMock(UserInterface::class);
        $user
            ->expects($this->any())
            ->method('getPassword')
            ->will($this->returnValue($password));

        $tokenUser = $this->createMock(UserInterface::class);
        $tokenUser
            ->expects($this->any())
            ->method('getPassword')
            ->will($this->returnValue($password));
        $token = new UsernamePasswordToken($tokenUser, 'foo', 'bar');

        $method = new \ReflectionMethod($this->authProvider, 'checkAuthentication');
        $method->setAccessible(true);
        $method->invoke($this->authProvider, $user, $token);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\BadCredentialsException
     */
    public function testCheckAuthenticationCredentialsChanged()
    {
        $apiUser = $this->getMockBuilder(APIUser::class)
            ->setConstructorArgs([['passwordHash' => 'some_encoded_password']])
            ->setMethods(['getUserId'])
            ->getMockForAbstractClass();
        $apiUser
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue(456));
        $tokenUser = new User($apiUser);
        $token = new UsernamePasswordToken($tokenUser, 'foo', 'bar');

        $renewedApiUser = $this->getMockBuilder(APIUser::class)
            ->setConstructorArgs([['passwordHash' => 'renewed_encoded_password']])
            ->getMockForAbstractClass();

        $user = $this->createMock(User::class);
        $user
            ->expects($this->any())
            ->method('getAPIUser')
            ->will($this->returnValue($renewedApiUser));

        $method = new \ReflectionMethod($this->authProvider, 'checkAuthentication');
        $method->setAccessible(true);
        $method->invoke($this->authProvider, $user, $token);
    }

    public function testCheckAuthenticationAlreadyLoggedIn()
    {
        $password = 'encoded_password';

        $apiUser = $this->getMockBuilder(APIUser::class)
            ->setConstructorArgs([['passwordHash' => $password]])
            ->setMethods(['getUserId'])
            ->getMockForAbstractClass();
        $tokenUser = new User($apiUser);
        $token = new UsernamePasswordToken($tokenUser, 'foo', 'bar');

        $user = $this->createMock(User::class);
        $user
            ->expects($this->once())
            ->method('getAPIUser')
            ->will($this->returnValue($apiUser));

        $this->repository
            ->expects($this->once())
            ->method('setCurrentUser')
            ->with($apiUser);

        $method = new \ReflectionMethod($this->authProvider, 'checkAuthentication');
        $method->setAccessible(true);
        $method->invoke($this->authProvider, $user, $token);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\BadCredentialsException
     */
    public function testCheckAuthenticationFailed()
    {
        $user = $this->createMock(User::class);
        $userName = 'my_username';
        $password = 'foo';
        $token = new UsernamePasswordToken($userName, $password, 'bar');

        $userService = $this->createMock(UserService::class);
        $userService
            ->expects($this->once())
            ->method('loadUserByCredentials')
            ->with($userName, $password)
            ->will($this->throwException(new NotFoundException('what', 'identifier')));
        $this->repository
            ->expects($this->once())
            ->method('getUserService')
            ->will($this->returnValue($userService));

        $method = new \ReflectionMethod($this->authProvider, 'checkAuthentication');
        $method->setAccessible(true);
        $method->invoke($this->authProvider, $user, $token);
    }

    public function testCheckAuthentication()
    {
        $user = $this->createMock(User::class);
        $userName = 'my_username';
        $password = 'foo';
        $token = new UsernamePasswordToken($userName, $password, 'bar');

        $apiUser = $this->getMockForAbstractClass(APIUser::class);
        $userService = $this->createMock(UserService::class);
        $userService
            ->expects($this->once())
            ->method('loadUserByCredentials')
            ->with($userName, $password)
            ->will($this->returnValue($apiUser));
        $this->repository
            ->expects($this->once())
            ->method('getUserService')
            ->will($this->returnValue($userService));
        $this->repository
            ->expects($this->once())
            ->method('setCurrentUser')
            ->with($apiUser);

        $method = new \ReflectionMethod($this->authProvider, 'checkAuthentication');
        $method->setAccessible(true);
        $method->invoke($this->authProvider, $user, $token);
    }
}
