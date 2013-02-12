<?php
/**
 * File containing the LegacyListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Security\Firewall;

use Symfony\Component\Security\Http\Firewall\AbstractPreAuthenticatedListener;
use Symfony\Component\HttpFoundation\Request;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use eZ\Publish\API\Repository\Repository;

class LegacyListener extends AbstractPreAuthenticatedListener
{
    /**
     * Gets the user and credentials from the Request.
     *
     * @param Request $request A Request instance
     *
     * @return array An array composed of the user and the credentials
     */
    protected function getPreAuthenticatedData( Request $request )
    {
        $userId = null;
        if ( $request->cookies->has( 'is_logged_in' ) && $request->cookies->get( 'is_logged_in' ) === 'true' )
        {
            $userId = $request->getSession()->get( 'eZUserLoggedInID' );
        }

        // -1 stands for not logged in user (aka anonymous), since PreAuthenticatedAuthenticationProvider needs a non empty value
        $userId = $userId ?: -1;

        return array( (string)$userId, '' );
    }
}
