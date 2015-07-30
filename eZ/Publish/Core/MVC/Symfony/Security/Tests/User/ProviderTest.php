<?php

/**
 * File containing the ProviderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\User;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\Security\User\Provider;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\User\User;
use PHPUnit_Framework_TestCase;

class ProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userService;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Security\User\Provider
     */
    private $userProvider;

    protected function setUp()
    {
        parent::setUp();
        $this->userService = $this->getMock('eZ\Publish\API\Repository\UserService');
        $this->repository = $this->getMock('eZ\Publish\API\Repository\Repository');
        $this->repository
            ->expects($this->any())
            ->method('getUserService')
            ->will($this->returnValue($this->userService));
        $this->userProvider = new Provider($this->repository);
    }

    public function testLoadUserByUsernameAlreadyUserObject()
    {
        $user = $this->getMock('eZ\Publish\Core\MVC\Symfony\Security\UserInterface');
        $this->assertSame($user, $this->userProvider->loadUserByUsername($user));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUserByUsernameUserNotFound()
    {
        $username = 'foobar';
        $this->userService
            ->expects($this->once())
            ->method('loadUserByLogin')
            ->with($username)
            ->will($this->throwException(new NotFoundException('user', $username)));
        $this->userProvider->loadUserByUsername($username);
    }

    public function testLoadUserByUsername()
    {
        $username = 'foobar';
        $apiUser = $this->getMock('eZ\Publish\API\Repository\Values\User\User');
        $this->userService
            ->expects($this->once())
            ->method('loadUserByLogin')
            ->with($username)
            ->will($this->returnValue($apiUser));

        $user = $this->userProvider->loadUserByUsername($username);
        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\Security\UserInterface', $user);
        $this->assertSame($apiUser, $user->getAPIUser());
        $this->assertSame(array('ROLE_USER'), $user->getRoles());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshUserNotSupported()
    {
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $this->userProvider->refreshUser($user);
    }

    public function testRefreshUser()
    {
        $userId = 123;
        $apiUser = new User(
            array(
                'content' => new Content(
                    array(
                        'versionInfo' => new VersionInfo(
                            array('contentInfo' => new ContentInfo(array('id' => $userId)))
                        ),
                    )
                ),
            )
        );
        $refreshedAPIUser = clone $apiUser;
        $user = $this->getMock('eZ\Publish\Core\MVC\Symfony\Security\UserInterface');
        $user
            ->expects($this->once())
            ->method('getAPIUser')
            ->will($this->returnValue($apiUser));
        $user
            ->expects($this->once())
            ->method('setAPIUser')
            ->with($refreshedAPIUser);

        $this->userService
            ->expects($this->once())
            ->method('loadUser')
            ->with($userId)
            ->will($this->returnValue($refreshedAPIUser));

        $this->repository
            ->expects($this->once())
            ->method('setCurrentUser')
            ->with($refreshedAPIUser);

        $this->assertSame($user, $this->userProvider->refreshUser($user));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testRefreshUserNotFound()
    {
        $userId = 123;
        $apiUser = new User(
            array(
                'content' => new Content(
                    array(
                        'versionInfo' => new VersionInfo(
                            array('contentInfo' => new ContentInfo(array('id' => $userId)))
                        ),
                    )
                ),
            )
        );
        $user = $this->getMock('eZ\Publish\Core\MVC\Symfony\Security\UserInterface');
        $user
            ->expects($this->once())
            ->method('getAPIUser')
            ->will($this->returnValue($apiUser));

        $this->userService
            ->expects($this->once())
            ->method('loadUser')
            ->with($userId)
            ->will($this->throwException(new NotFoundException('user', 'foo')));

        $this->userProvider->refreshUser($user);
    }

    /**
     * @dataProvider supportsClassProvider
     */
    public function testSupportsClass($class, $supports)
    {
        $this->assertSame($supports, $this->userProvider->supportsClass($class));
    }

    public function supportsClassProvider()
    {
        return array(
            array('Symfony\Component\Security\Core\User\UserInterface', false),
            array('eZ\Publish\Core\MVC\Symfony\Security\User', true),
            array(get_class($this->getMock('eZ\Publish\Core\MVC\Symfony\Security\User')), true),
        );
    }

    public function testLoadUserByAPIUser()
    {
        $apiUser = $this->getMock('eZ\Publish\API\Repository\Values\User\User');
        $user = $this->userProvider->loadUserByAPIUser($apiUser);
        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\Security\User', $user);
        $this->assertSame($apiUser, $user->getAPIUser());
    }
}
