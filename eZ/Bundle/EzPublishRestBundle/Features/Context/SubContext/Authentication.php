<?php
/**
 * File containing the Authentication context class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use EzSystems\BehatBundle\Sentence\Authentication as AuthenticationSentences;
use Behat\Behat\Exception\PendingException;

class Authentication extends Base implements AuthenticationSentences
{
    public function iAmLoggedInAsAn( $role )
    {
        switch( strtolower( $role ) )
        {
            case 'administrator':
                $user = 'admin';
                $password = 'publish';
                break;

            default:
                throw new PendingException( "Login with '$role' role not implemented yet" );
        }

        $this->restClient->setAuthentication( $user, $password );
    }

    public function iAmLoggedInAsWithPassword( $user, $password )
    {
        $this->restClient->setAuthentication( $user, $password );
    }

    public function iAmNotLoggedIn()
    {
        $this->restClient->setAuthentication( '', '' );
    }
}
