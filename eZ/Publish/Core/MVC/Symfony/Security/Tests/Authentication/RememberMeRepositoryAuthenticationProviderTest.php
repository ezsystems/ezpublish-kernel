<?php

/**
 * File containing the RememberMeRepositoryAuthenticationProviderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\Authentication;

use eZ\Publish\Core\MVC\Symfony\Security\Authentication\RememberMeRepositoryAuthenticationProvider;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;

class RememberMeRepositoryAuthenticationProviderTest extends TestCase
{
    /**
     * @var RememberMeRepositoryAuthenticationProvider
     */
    private $authProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\Repository
     */
    private $repository;

    protected function setUp()
    {
        parent::setUp();

        $this->repository = $this->getMock('eZ\Publish\API\Repository\Repository');
        $this->authProvider = new RememberMeRepositoryAuthenticationProvider(
            $this->getMock('Symfony\Component\Security\Core\User\UserCheckerInterface'),
            'my secret',
            'my provider secret'
        );
        $this->authProvider->setRepository($this->repository);
    }

    public function testAuthenticateUnsupportedToken()
    {
        $anonymousToken = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\AnonymousToken')
            ->setConstructorArgs(['secret', $this->getMock('Symfony\Component\Security\Core\User\UserInterface')])
            ->getMock();
        $this->assertNull($this->authProvider->authenticate($anonymousToken));
    }

    public function testAuthenticateWrongProviderKey()
    {
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $user
            ->expects($this->any())
            ->method('getRoles')
            ->will($this->returnValue([]));

        $rememberMeToken = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\RememberMeToken')
            ->setConstructorArgs([$user, 'wrong provider secret', 'my secret'])
            ->getMock();
        $rememberMeToken
            ->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue('wrong provider secret'));

        $this->assertNull($this->authProvider->authenticate($rememberMeToken));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\BadCredentialsException
     */
    public function testAuthenticateWrongSecret()
    {
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $user
            ->expects($this->any())
            ->method('getRoles')
            ->will($this->returnValue([]));

        $rememberMeToken = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\RememberMeToken')
            ->setConstructorArgs([$user, 'my provider secret', 'the wrong secret'])
            ->getMock();
        $rememberMeToken
            ->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue('my provider secret'));
        $rememberMeToken
            ->expects($this->any())
            ->method('getSecret')
            ->will($this->returnValue('the wrong secret'));

        $this->authProvider->authenticate($rememberMeToken);
    }

    public function testAuthenticate()
    {
        $this->repository
            ->expects($this->once())
            ->method('getPermissionResolver')
            ->will($this->returnValue($this->getPermissionResolverMock()));

        $apiUser = $this->getMock('eZ\Publish\API\Repository\Values\User\User');
        $apiUser
            ->expects($this->any())
            ->method('getUserId')
            ->will($this->returnValue(42));

        $tokenUser = new User($apiUser);
        $rememberMeToken = new RememberMeToken($tokenUser, 'my provider secret', 'my secret');

        $authenticatedToken = $this->authProvider->authenticate($rememberMeToken);
        $this->assertEquals(
            [$rememberMeToken->getProviderKey(), $rememberMeToken->getSecret(), $rememberMeToken->getUsername()],
            [$authenticatedToken->getProviderKey(), $authenticatedToken->getSecret(), $authenticatedToken->getUsername()]
        );
    }

    /**
     * @return \eZ\Publish\Core\Repository\Permission\PermissionResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPermissionResolverMock()
    {
        return $this
            ->getMockBuilder('eZ\Publish\Core\Repository\Permission\PermissionResolver')
            ->setMethods(null)
            ->setConstructorArgs(
                [
                    $this
                        ->getMockBuilder('eZ\Publish\Core\Repository\Helper\RoleDomainMapper')
                        ->disableOriginalConstructor()
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\Core\Repository\Helper\LimitationService')
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\SPI\Persistence\User\Handler')
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\API\Repository\Values\User\UserReference')
                        ->getMock(),
                ]
            )
            ->getMock();
    }
}
