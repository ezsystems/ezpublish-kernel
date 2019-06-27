<?php

/**
 * File containing the AnonymousAuthenticationProviderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\Authentication;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Security\Authentication\AnonymousAuthenticationProvider;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\User\UserInterface;

class AnonymousAuthenticationProviderTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->createMock(Repository::class);
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
    }

    public function testAuthenticate()
    {
        $anonymousUserId = 10;
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('anonymous_user_id')
            ->will($this->returnValue($anonymousUserId));

        $this->repository
            ->expects($this->once())
            ->method('setCurrentUser')
            ->with(new UserReference($anonymousUserId));

        $key = 'some_key';
        $authProvider = new AnonymousAuthenticationProvider($key);
        $authProvider->setRepository($this->repository);
        $authProvider->setConfigResolver($this->configResolver);
        $anonymousToken = $this
            ->getMockBuilder(AnonymousToken::class)
            ->setConstructorArgs([$key, $this->createMock(UserInterface::class)])
            ->getMockForAbstractClass();
        $this->assertSame($anonymousToken, $authProvider->authenticate($anonymousToken));
    }
}
