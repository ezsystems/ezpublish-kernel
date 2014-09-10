<?php
/**
 * File containing the Authentication context class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use Behat\Behat\Tester\Exception\PendingException;

trait Authentication
{
    /**
     * @Given I have :role permissions
     */
    public function usePermissionsOfRole( $role )
    {
        $credentials = $this->getCredentialsFor( $role );

        $this->restDriver->setAuthentication(
            $credentials['login'],
            $credentials['password']
        );
    }

    /**
     * @Given I don't have permissions
     * @Given I do not have permissions
     */
    public function useAnonymousRole()
    {
        $this->restDriver->setAuthentication( '', '' );
    }
}
