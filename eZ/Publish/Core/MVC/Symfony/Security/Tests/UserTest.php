<?php

/**
 * File containing the UserTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\Core\MVC\Symfony\Security\ReferenceUserInterface;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use PHPUnit\Framework\TestCase;
use eZ\Publish\Core\MVC\Symfony\Security\User;

class UserTest extends TestCase
{
    public function testConstruct()
    {
        $login = 'my_username';
        $passwordHash = 'encoded_password';
        $apiUser = $this
            ->getMockBuilder(APIUser::class)
            ->setConstructorArgs(
                [
                    [
                        'login' => $login,
                        'passwordHash' => $passwordHash,
                        'enabled' => true,
                    ],
                ]
            )
            ->setMethods(['getUserId'])
            ->getMockForAbstractClass();

        $roles = ['ROLE_USER'];
        $apiUser
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue(42));

        $user = new User($apiUser, $roles);
        $this->assertSame($apiUser, $user->getAPIUser());
        $this->assertSame($login, $user->getUsername());
        $this->assertSame($passwordHash, $user->getPassword());
        $this->assertSame($roles, $user->getRoles());
        $this->assertNull($user->getSalt());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertTrue($user->isAccountNonLocked());
        $this->assertTrue($user->isCredentialsNonExpired());
        $this->assertTrue($user->isEnabled());
    }

    public function testIsEqualTo()
    {
        $userId = 123;
        $apiUser = $this->createMock(APIUser::class);
        $apiUser
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($userId));
        $roles = ['ROLE_USER'];

        $user = new User($apiUser, $roles);

        $apiUser2 = $this->createMock(APIUser::class);
        $apiUser2
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($userId));
        $user2 = new User($apiUser2, []);

        $this->assertTrue($user->isEqualTo($user2));
    }

    public function testIsNotEqualTo()
    {
        $apiUser = $this->createMock(APIUser::class);
        $apiUser
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue(123));
        $roles = ['ROLE_USER'];

        $user = new User($apiUser, $roles);

        $apiUser2 = $this->createMock(APIUser::class);
        $apiUser2
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue(456));
        $user2 = new User($apiUser2, []);

        $this->assertFalse($user->isEqualTo($user2));
    }

    public function testIsEqualToNotSameUserType()
    {
        $user = new User();
        $user2 = $this->createMock(ReferenceUserInterface::class);
        $user2
            ->expects($this->once())
            ->method('getAPIUserReference')
            ->willReturn(new UserReference(456));
        $this->assertFalse($user->isEqualTo($user2));
    }

    public function testSetAPIUser()
    {
        $apiUser = $this->createMock(APIUser::class);
        $user = new User();
        $user->setAPIUser($apiUser);
        $this->assertSame($apiUser, $user->getAPIUser());
    }

    public function testToString()
    {
        $fullName = 'My full name';
        $userContentInfo = $this
            ->getMockBuilder(ContentInfo::class)
            ->setConstructorArgs([['name' => $fullName]])
            ->getMockForAbstractClass();
        $apiUser = $this->createMock(APIUser::class);
        $apiUser
            ->expects($this->any())
            ->method('__get')
            ->with('contentInfo')
            ->will($this->returnValue($userContentInfo));

        $user = new User($apiUser);
        $this->assertSame($fullName, (string)$user);
    }
}
