<?php

/**
 * File containing the RestLogoutHandlerTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */
namespace eZ\Publish\Core\REST\Server\Tests\Security;

use eZ\Publish\Core\REST\Server\Security\CsrfTokenManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class CsrfTokenManagerTest extends TestCase
{
    const CSRF_TOKEN_INTENTION = 'csrf';

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface */
    private $tokenStorage;
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    protected function setUp()
    {
        parent::setUp();

        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
    }

    public function testHasTokenForHttp()
    {
        $csrfTokenManager = $this->createCsrfTokenManager(false);

        $this->tokenStorage
            ->expects($this->once())
            ->method('hasToken')
            ->with(self::CSRF_TOKEN_INTENTION);

        $csrfTokenManager->hasToken(self::CSRF_TOKEN_INTENTION);
    }

    public function testHasTokenForHttps()
    {
        $csrfTokenManager = $this->createCsrfTokenManager(true);

        $this->tokenStorage
            ->expects($this->once())
            ->method('hasToken')
            ->with('https-' . self::CSRF_TOKEN_INTENTION);

        $csrfTokenManager->hasToken(self::CSRF_TOKEN_INTENTION);
    }

    private function createCsrfTokenManager($https = false)
    {
        $request = new Request();
        if ($https) {
            $request->server->set('HTTPS', 'ON');
        }

        $this->requestStack
            ->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn($request);

        return new CsrfTokenManager(
            $this->createMock(TokenGeneratorInterface::class),
            $this->tokenStorage,
            $this->requestStack
        );
    }
}
