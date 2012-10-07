<?php
/**
 * File containing the APIProviderInterface class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\User;

use eZ\Publish\API\Repository\Values\User\User as APIUser,
    Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Interface adding eZ Publish API specific methods to Symfony UserProviderInterface
 */
interface APIUserProviderInterface extends UserProviderInterface
{
    /**
     * Loads a regular user object, usable by Symfony Security component, from a user object returned by Public API
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser
     * @return \eZ\Publish\Core\MVC\Symfony\Security\User
     */
    public function loadUserByAPIUser( APIUser $apiUser );
}
