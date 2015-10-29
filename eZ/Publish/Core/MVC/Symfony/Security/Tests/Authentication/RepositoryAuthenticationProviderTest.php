<?php

/**
 * File containing the RepositoryAuthenticationProviderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\Authentication;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\Security\Authentication\RepositoryAuthenticationProvider;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use eZ\Publish\Core\MVC\Symfony\Security\User;

class RepositoryAuthenticationProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var RepositoryAuthenticationProvider
     */
    private $authProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\Repository
     */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->encoderFactory = $this->getMock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $repository = $this->repository = $this->getMock('eZ\Publish\API\Repository\Repository');
        $this->authProvider = new RepositoryAuthenticationProvider(
            $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface'),
            $this->getMock('Symfony\Component\Security\Core\User\UserCheckerInterface'),
            'foo',
            $this->encoderFactory
        );
        $this->authProvider->setRepository($repository);
    }

    public function testAuthenticationNotEzUser()
    {
        $password = 'some_encoded_password';
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $user
            ->expects($this->any())
            ->method('getPassword')
            ->will($this->returnValue($password));

        $tokenUser = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
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
        $apiUser = $this->getMockBuilder('eZ\Publish\API\Repository\Values\User\User')
            ->setConstructorArgs(array(array('passwordHash' => 'some_encoded_password')))
            ->getMockForAbstractClass();
        $tokenUser = new User($apiUser);
        $token = new UsernamePasswordToken($tokenUser, 'foo', 'bar');

        $renewedApiUser = $this->getMockBuilder('eZ\Publish\API\Repository\Values\User\User')
            ->setConstructorArgs(array(array('passwordHash' => 'renewed_encoded_password')))
            ->getMockForAbstractClass();

        $user = $this->getMock('eZ\Publish\Core\MVC\Symfony\Security\User');
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

        $apiUser = $this->getMockBuilder('eZ\Publish\API\Repository\Values\User\User')
            ->setConstructorArgs(array(array('passwordHash' => $password)))
            ->getMockForAbstractClass();
        $tokenUser = new User($apiUser);
        $token = new UsernamePasswordToken($tokenUser, 'foo', 'bar');

        $user = $this->getMock('eZ\Publish\Core\MVC\Symfony\Security\User');
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
        $user = $this->getMock('eZ\Publish\Core\MVC\Symfony\Security\User');
        $userName = 'my_username';
        $password = 'foo';
        $token = new UsernamePasswordToken($userName, $password, 'bar');

        $userService = $this->getMock('eZ\Publish\API\Repository\UserService');
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
        $user = $this->getMock('eZ\Publish\Core\MVC\Symfony\Security\User');
        $userName = 'my_username';
        $password = 'foo';
        $token = new UsernamePasswordToken($userName, $password, 'bar');

        $apiUser = $this->getMockForAbstractClass('eZ\Publish\API\Repository\Values\User\User');
        $userService = $this->getMock('eZ\Publish\API\Repository\UserService');
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
