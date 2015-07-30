<?php

/**
 * File containing the UserWrappedTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests;

use eZ\Publish\Core\MVC\Symfony\Security\UserWrapped;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Security\Core\User\User;

class UserWrappedTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $apiUser;

    protected function setUp()
    {
        parent::setUp();
        $this->apiUser = $this->getMock('eZ\Publish\API\Repository\Values\User\User');
    }

    public function testGetSetAPIUser()
    {
        $originalUser = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $userWrapped = new UserWrapped($originalUser, $this->apiUser);
        $this->assertSame($this->apiUser, $userWrapped->getAPIUser());

        $newApiUser = $this->getMock('eZ\Publish\API\Repository\Values\User\User');
        $userWrapped->setAPIUser($newApiUser);
        $this->assertSame($newApiUser, $userWrapped->getAPIUser());
    }

    public function testGetSetWrappedUser()
    {
        $originalUser = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $userWrapped = new UserWrapped($originalUser, $this->apiUser);
        $this->assertSame($originalUser, $userWrapped->getWrappedUser());

        $newWrappedUser = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $userWrapped->setWrappedUser($newWrappedUser);
        $this->assertSame($newWrappedUser, $userWrapped->getWrappedUser());
    }

    /**
     * @dataProvider advancedUserProvider
     */
    public function testAdvancedUser($username, $password, $roles, $enabled, $userNonExpired, $credentialsNonExpired, $userNonLocked)
    {
        $originalUser = new User($username, $password, $roles, $enabled, $userNonExpired, $credentialsNonExpired, $userNonLocked);
        $user = new UserWrapped($originalUser, $this->apiUser);
        $this->assertSame($username, (string)$user);
        $this->assertSame($username, $user->getUsername());
        $this->assertSame($password, $user->getPassword());
        $this->assertSame($roles, $user->getRoles());
        $this->assertSame($enabled, $user->isEnabled());
        $this->assertSame($userNonExpired, $user->isAccountNonExpired());
        $this->assertSame($credentialsNonExpired, $user->isCredentialsNonExpired());
        $this->assertSame($userNonLocked, $user->isAccountNonLocked());
        $this->assertSame($originalUser->getSalt(), $user->getSalt());
        $this->assertSame($originalUser->getUsername(), $user->getWrappedUser()->getUsername());
        $this->assertSame($originalUser->isEnabled(), $user->getWrappedUser()->isEnabled());
        $this->assertSame($originalUser, $user->getWrappedUser());
    }

    public function advancedUserProvider()
    {
        return array(
            array('foo', 'password', array('ROLE_USER'), true, true, true, true),
            array('foo', 'password', array('ROLE_USER'), true, false, true, false),
            array('foo', 'password', array('ROLE_USER'), false, true, false, true),
            array('bar', 'secret', array('ROLE_TEST'), true, true, true, true),
            array('bar', 'secret', array('ROLE_TEST'), false, false, false, false),
            array('Jérôme', 'NoThisIsNotMyRealPassword', array('ROLE_ADMIN'), true, true, true, true),
        );
    }

    public function testRegularUser()
    {
        $originalUser = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $user = new UserWrapped($originalUser, $this->apiUser);

        $this->assertTrue($user->isEnabled());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertTrue($user->isAccountNonLocked());
        $this->assertTrue($user->isCredentialsNonExpired());
        $this->assertTrue($user->isEqualTo($this->getMock('Symfony\Component\Security\Core\User\UserInterface')));

        $originalUser
            ->expects($this->once())
            ->method('eraseCredentials');
        $user->eraseCredentials();

        $username = 'lolautruche';
        $password = 'NoThisIsNotMyRealPassword';
        $roles = array('ROLE_USER', 'ROLE_TEST');
        $salt = md5(microtime(true));
        $originalUser
            ->expects($this->exactly(2))
            ->method('getUsername')
            ->will($this->returnValue($username));
        $originalUser
            ->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue($password));
        $originalUser
            ->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue($roles));
        $originalUser
            ->expects($this->once())
            ->method('getSalt')
            ->will($this->returnValue($salt));

        $this->assertSame($username, $user->getUsername());
        $this->assertSame($username, (string)$user);
        $this->assertSame($password, $user->getPassword());
        $this->assertSame($roles, $user->getRoles());
        $this->assertSame($salt, $user->getSalt());
        $this->assertSame($originalUser, $user->getWrappedUser());
    }

    public function testIsEqualTo()
    {
        $originalUser = $this->getMock('eZ\Publish\Core\MVC\Symfony\Security\User');
        $user = new UserWrapped($originalUser, $this->apiUser);
        $otherUser = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $originalUser
            ->expects($this->once())
            ->method('isEqualTo')
            ->with($otherUser)
            ->will($this->returnValue(false));
        $this->assertFalse($user->isEqualTo($otherUser));
    }
}
