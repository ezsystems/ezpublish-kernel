<?php
/**
 * File containing the AuthenticationContext class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext\RestSubContext;
use EzSystems\BehatBundle\Features\Context\SentencesInterfaces\Authentication;
use Behat\Behat\Exception\PendingException;

/**
 * AuthenticationContext
 *
 * This class contains the implementation of the Authentication interface which
 * has the sentences for the Authentication BDD
 */
class AuthenticationContext extends RestSubContext implements Authentication
{
    public function iAmLoggedInAsAn( $role )
    {
        switch( strtolower( $role ) )
        {
            case 'administrator':
                $user = 'admin';
                $passwd = 'publish';
                break;

            default:
                throw new PendingException( "Login with '$role' role not implemented yet" );
        }

        $this->restclient->setAuthentication( $user, $passwd );
    }

    public function iAmLoggedInAsWithPassword( $user, $password )
    {
        $this->restclient->setAuthentication( $user, $password );
    }

    public function iAmNotLoggedIn()
    {
        $this->restclient->setAuthentication( '', '' );
    }
}
