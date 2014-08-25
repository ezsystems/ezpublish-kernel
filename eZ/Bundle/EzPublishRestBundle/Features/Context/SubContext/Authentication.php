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
    /**
     * Given I am logged in as an|a "<role>"
     * Given I have "<role>" permissions
     */
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

        $this->restDriver->setAuthentication( $user, $password );
    }

    /**
     * Given I am logged in as "<user>" with password "<password>"
     */
    public function iAmLoggedInAsWithPassword( $user, $password )
    {
        $this->restDriver->setAuthentication( $user, $password );
    }

    /**
     * Given I am not logged in
     * Given I do not|don't have permissions
     */
    public function iAmNotLoggedIn()
    {
        $this->restDriver->setAuthentication( '', '' );
    }
}
