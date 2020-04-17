<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests;

use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface;
use eZ\Publish\Core\MVC\Symfony\Security\UserWrapped;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class UserWrappedTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $apiUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiUser = $this->createMock(APIUser::class);
    }

    public function testGetSetAPIUser()
    {
        $originalUser = $this->createMock(SymfonyUserInterface::class);
        $userWrapped = new UserWrapped($originalUser, $this->apiUser);
        $this->assertSame($this->apiUser, $userWrapped->getAPIUser());

        $newApiUser = $this->createMock(APIUser::class);
        $userWrapped->setAPIUser($newApiUser);
        $this->assertSame($newApiUser, $userWrapped->getAPIUser());
    }

    public function testGetSetWrappedUser()
    {
        $originalUser = $this->createMock(SymfonyUserInterface::class);
        $userWrapped = new UserWrapped($originalUser, $this->apiUser);
        $this->assertSame($originalUser, $userWrapped->getWrappedUser());

        $newWrappedUser = $this->createMock(UserInterface::class);
        $userWrapped->setWrappedUser($newWrappedUser);
        $this->assertSame($newWrappedUser, $userWrapped->getWrappedUser());
    }

    public function testRegularUser()
    {
        $originalUser = $this->createMock(SymfonyUserInterface::class);
        $user = new UserWrapped($originalUser, $this->apiUser);

        $this->assertTrue($user->isEqualTo($this->createMock(SymfonyUserInterface::class)));

        $originalUser
            ->expects($this->once())
            ->method('eraseCredentials');
        $user->eraseCredentials();

        $username = 'lolautruche';
        $password = 'NoThisIsNotMyRealPassword';
        $roles = ['ROLE_USER', 'ROLE_TEST'];
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
        $originalUser = $this->createMock(UserEquatableInterface::class);
        $user = new UserWrapped($originalUser, $this->apiUser);
        $otherUser = $this->createMock(SymfonyUserInterface::class);
        $originalUser
            ->expects($this->once())
            ->method('isEqualTo')
            ->with($otherUser)
            ->will($this->returnValue(false));
        $this->assertFalse($user->isEqualTo($otherUser));
    }
}

/**
 * @internal For use with tests only
 */
interface UserEquatableInterface extends UserInterface, EquatableInterface
{
}
