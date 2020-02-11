<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\User;

use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\Security\User\UsernameProvider;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\MVC\Symfony\Security\User as MVCUser;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class UsernameProviderTest extends TestCase
{
    /** @var \eZ\Publish\API\Repository\UserService|\PHPUnit\Framework\MockObject\MockObject */
    private $userService;

    /** @var \eZ\Publish\API\Repository\PermissionResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $permissionResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\Security\User\UsernameProvider */
    private $userProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = $this->createMock(UserService::class);
        $this->permissionResolver = $this->createMock(PermissionResolver::class);
        $this->userProvider = new UsernameProvider($this->userService, $this->permissionResolver);
    }

    public function testLoadUserByUsernameAlreadyUserObject()
    {
        $user = $this->createMock(UserInterface::class);
        $this->assertSame($user, $this->userProvider->loadUserByUsername($user));
    }

    public function testLoadUserByUsernameUserNotFound()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\UsernameNotFoundException::class);

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

    public function testRefreshUserNotSupported()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\UnsupportedUserException::class);

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

        $this->permissionResolver
            ->expects($this->once())
            ->method('setCurrentUserReference')
            ->with(new UserReference($apiUser->getUserId()));

        $this->assertSame($user, $this->userProvider->refreshUser($user));
    }

    public function testRefreshUserNotFound()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\UsernameNotFoundException::class);

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
        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }
}
