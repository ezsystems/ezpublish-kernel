<?php

/**
 * File containing the ProviderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\User;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\Security\User\Provider;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\MVC\Symfony\Security\User as MVCUser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class ProviderTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $userService;

    /** @var \eZ\Publish\Core\MVC\Symfony\Security\User\Provider */
    private $userProvider;

    protected function setUp()
    {
        parent::setUp();
        $this->userService = $this->createMock(UserService::class);
        $this->repository = $this->createMock(Repository::class);
        $this->repository
            ->expects($this->any())
            ->method('getUserService')
            ->will($this->returnValue($this->userService));
        $this->userProvider = new Provider($this->repository);
    }

    public function testLoadUserByUsernameAlreadyUserObject()
    {
        $user = $this->createMock(UserInterface::class);
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
        $apiUser = $this->createMock(APIUser::class);
        $this->userService
            ->expects($this->once())
            ->method('loadUserByLogin')
            ->with($username)
            ->will($this->returnValue($apiUser));

        $user = $this->userProvider->loadUserByUsername($username);
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertSame($apiUser, $user->getAPIUser());
        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshUserNotSupported()
    {
        $user = $this->createMock(SymfonyUserInterface::class);
        $this->userProvider->refreshUser($user);
    }

    public function testRefreshUser()
    {
        $userId = 123;
        $apiUser = new User(
            [
                'content' => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            ['contentInfo' => new ContentInfo(['id' => $userId])]
                        ),
                    ]
                ),
            ]
        );
        $refreshedAPIUser = clone $apiUser;
        $user = $this->createMock(UserInterface::class);
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
            [
                'content' => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            ['contentInfo' => new ContentInfo(['id' => $userId])]
                        ),
                    ]
                ),
            ]
        );
        $user = $this->createMock(UserInterface::class);
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
        return [
            [SymfonyUserInterface::class, false],
            [MVCUser::class, true],
            [get_class($this->createMock(MVCUser::class)), true],
        ];
    }

    public function testLoadUserByAPIUser()
    {
        $apiUser = $this->createMock(APIUser::class);
        $user = $this->userProvider->loadUserByAPIUser($apiUser);
        $this->assertInstanceOf(MVCUser::class, $user);
        $this->assertSame($apiUser, $user->getAPIUser());
    }
}
