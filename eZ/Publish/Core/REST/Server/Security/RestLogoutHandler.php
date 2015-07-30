<?php

/**
 * File containing the RestLogoutHandler class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Logout handler used by REST session based logout.
 * It forces session cookie clearing.
 */
class RestLogoutHandler implements LogoutHandlerInterface
{
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        if (!$request->attributes->get('is_rest_request')) {
            return;
        }

        $response->headers->clearCookie($request->getSession()->getName());
    }
}
