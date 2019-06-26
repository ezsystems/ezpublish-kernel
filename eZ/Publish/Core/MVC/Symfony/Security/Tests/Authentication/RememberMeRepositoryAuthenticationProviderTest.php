<?php

/**
 * File containing the RememberMeRepositoryAuthenticationProviderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\Authentication;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\User\User as ApiUser;
use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\Core\MVC\Symfony\Security\Authentication\RememberMeRepositoryAuthenticationProvider;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use eZ\Publish\Core\Repository\Helper\LimitationService;
use eZ\Publish\Core\Repository\Helper\RoleDomainMapper;
use eZ\Publish\Core\Repository\Permission\PermissionResolver;
use eZ\Publish\SPI\Persistence\User\Handler as UserHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class RememberMeRepositoryAuthenticationProviderTest extends TestCase
{
    /** @var RememberMeRepositoryAuthenticationProvider */
    private $authProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\API\Repository\Repository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();

        $this->repository = $this->createMock(Repository::class);
        $this->authProvider = new RememberMeRepositoryAuthenticationProvider(
            $this->createMock(UserCheckerInterface::class),
            'my secret',
            'my provider secret'
        );
        $this->authProvider->setRepository($this->repository);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage The token is not supported by this authentication provider.
     */
    public function testAuthenticateUnsupportedToken()
    {
        $anonymousToken = $this
            ->getMockBuilder(AnonymousToken::class)
            ->setConstructorArgs(['secret', $this->createMock(UserInterface::class)])
            ->getMock();
        $this->authProvider->authenticate($anonymousToken);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage The token is not supported by this authentication provider.
     */
    public function testAuthenticateWrongProviderKey()
    {
        $user = $this->createMock(UserInterface::class);
        $user
            ->expects($this->any())
            ->method('getRoles')
            ->will($this->returnValue([]));

        $rememberMeToken = $this
            ->getMockBuilder(RememberMeToken::class)
            ->setConstructorArgs([$user, 'wrong provider secret', 'my secret'])
            ->getMock();
        $rememberMeToken
            ->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue('wrong provider secret'));

        $this->authProvider->authenticate($rememberMeToken);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\BadCredentialsException
     */
    public function testAuthenticateWrongSecret()
    {
        $user = $this->createMock(UserInterface::class);
        $user
            ->expects($this->any())
            ->method('getRoles')
            ->will($this->returnValue([]));

        $rememberMeToken = $this
            ->getMockBuilder(RememberMeToken::class)
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

        $apiUser = $this->createMock(ApiUser::class);
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
     * @return \eZ\Publish\Core\Repository\Permission\PermissionResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getPermissionResolverMock()
    {
        return $this
            ->getMockBuilder(PermissionResolver::class)
            ->setMethods(null)
            ->setConstructorArgs(
                [
                    $this
                        ->getMockBuilder(RoleDomainMapper::class)
                        ->disableOriginalConstructor()
                        ->getMock(),
                    $this
                        ->getMockBuilder(LimitationService::class)
                        ->getMock(),
                    $this
                        ->getMockBuilder(UserHandler::class)
                        ->getMock(),
                    $this
                        ->getMockBuilder(UserReference::class)
                        ->getMock(),
                ]
            )
            ->getMock();
    }
}
