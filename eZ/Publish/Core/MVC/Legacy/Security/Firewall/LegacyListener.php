<?php
/**
 * File containing the LegacyListener class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Security\Firewall;

use eZ\Publish\Core\MVC\Symfony\Security\Firewall\Listener as BaseListener,
    eZ\Publish\Core\MVC\Symfony\Security\Authentication\Token,
    Symfony\Component\Security\Core\Exception\AuthenticationException,
    Symfony\Component\HttpFoundation\Request;

class LegacyListener extends BaseListener
{
    /**
     * Performs authentication.
     *
     * @param Request $request A Request instance
     *
     * @return TokenInterface|Response|null The authenticated token, null if full authentication is not possible, or a Response
     *
     * @throws AuthenticationException if the authentication fails
     */
    protected function attemptAuthentication( Request $request )
    {
        if ( $request->cookies->has( 'is_logged_in' ) && $request->cookies->get( 'is_logged_in' ) === 'true' )
        {
            return $this->authenticationManager->authenticate(
                new Token( $request->getSession()->get( 'eZUserLoggedInID' ) )
            );
        }

        throw new AuthenticationException( 'Cannot authenticate current user.' );
    }
}
