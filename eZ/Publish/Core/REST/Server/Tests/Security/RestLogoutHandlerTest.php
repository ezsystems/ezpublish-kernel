<?php

/**
 * File containing the RestLogoutHandlerTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */
namespace eZ\Publish\Core\REST\Server\Tests\Security;

use eZ\Publish\Core\REST\Server\Security\RestLogoutHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RestLogoutHandlerTest extends TestCase
{
    public function testLogout()
    {
        $session = $this->createMock(SessionInterface::class);
        $sessionId = 'eZSESSID';
        $session
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($sessionId));

        $request = new Request();
        $request->setSession($session);
        $request->attributes->set('is_rest_request', true);

        $response = new Response();
        $response->headers = $this->createMock(ResponseHeaderBag::class);
        $response->headers
            ->expects($this->once())
            ->method('clearCookie')
            ->with($sessionId);

        $logoutHandler = new RestLogoutHandler();
        $logoutHandler->logout(
            $request,
            $response,
            $this->createMock(TokenInterface::class)
        );
    }

    public function testLogoutNotRest()
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->never())
            ->method('getName');

        $request = new Request();
        $request->setSession($session);

        $response = new Response();
        $response->headers = $this->createMock(ResponseHeaderBag::class);
        $response->headers
            ->expects($this->never())
            ->method('clearCookie');

        $logoutHandler = new RestLogoutHandler();
        $logoutHandler->logout(
            $request,
            $response,
            $this->createMock(TokenInterface::class)
        );
    }
}
