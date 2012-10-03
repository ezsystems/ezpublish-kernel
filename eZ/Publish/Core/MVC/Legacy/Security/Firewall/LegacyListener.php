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
    Symfony\Component\Security\Core\Exception\AuthenticationException,
    Symfony\Component\HttpFoundation\Request;

class LegacyListener extends BaseListener
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \eZ\Publish\API\Repository\Values\User\User|null
     */
    protected function getCurrentUserId( Request $request )
    {
        if ( $request->cookies->has( 'is_logged_in' ) && $request->cookies->get( 'is_logged_in' ) === 'true' )
        {
            return $request->getSession()->get( 'eZUserLoggedInID' );
        }

        return null;
    }
}
