<?php

/**
 * File containing the UserTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests;

use PHPUnit_Framework_TestCase;
use eZ\Publish\Core\MVC\Symfony\Security\User;

class UserTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $login = 'my_username';
        $passwordHash = 'encoded_password';
        $apiUser = $this
            ->getMockBuilder('eZ\Publish\API\Repository\Values\User\User')
            ->setConstructorArgs(
                array(
                    array(
                        'login' => $login,
                        'passwordHash' => $passwordHash,
                        'enabled' => true,
                    ),
                )
            )
            ->getMockForAbstractClass();
        $roles = array('ROLE_USER');

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
        $apiUser = $this->getMock('eZ\Publish\API\Repository\Values\User\User');
        $apiUser
            ->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue($userId));
        $roles = array('ROLE_USER');

        $user = new User($apiUser, $roles);

        $apiUser2 = $this->getMock('eZ\Publish\API\Repository\Values\User\User');
        $apiUser2
            ->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue($userId));
        $user2 = new User($apiUser2, array());

        $this->assertTrue($user->isEqualTo($user2));
    }

    public function testIsNotEqualTo()
    {
        $apiUser = $this->getMock('eZ\Publish\API\Repository\Values\User\User');
        $apiUser
            ->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(123));
        $roles = array('ROLE_USER');

        $user = new User($apiUser, $roles);

        $apiUser2 = $this->getMock('eZ\Publish\API\Repository\Values\User\User');
        $apiUser2
            ->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(456));
        $user2 = new User($apiUser2, array());

        $this->assertFalse($user->isEqualTo($user2));
    }

    public function testIsEqualToNotSameUserType()
    {
        $user = new User();
        $user2 = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $this->assertFalse($user->isEqualTo($user2));
    }

    public function testSetAPIUser()
    {
        $apiUser = $this->getMock('eZ\Publish\API\Repository\Values\User\User');
        $user = new User();
        $user->setAPIUser($apiUser);
        $this->assertSame($apiUser, $user->getAPIUser());
    }

    public function testToString()
    {
        $fullName = 'My full name';
        $userContentInfo = $this
            ->getMockBuilder('eZ\Publish\API\Repository\Values\Content\ContentInfo')
            ->setConstructorArgs(array(array('name' => $fullName)))
            ->getMockForAbstractClass();
        $apiUser = $this->getMock('eZ\Publish\API\Repository\Values\User\User');
        $apiUser
            ->expects($this->any())
            ->method('__get')
            ->with('contentInfo')
            ->will($this->returnValue($userContentInfo));

        $user = new User($apiUser);
        $this->assertSame($fullName, (string)$user);
    }
}
