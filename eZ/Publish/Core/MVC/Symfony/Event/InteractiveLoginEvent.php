<?php
/**
 * File containing the InteractiveLoginEvent class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Event;

use eZ\Publish\API\Repository\Values\User\User as APIUser;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent as BaseInteractiveLoginEvent;

class InteractiveLoginEvent extends BaseInteractiveLoginEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    private $apiUser;

    /**
     * Checks if an API user has been provided.
     *
     * @return bool
     */
    public function hasAPIUser()
    {
        return isset( $this->apiUser );
    }

    /**
     * Injects an API user to be injected in the repository.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser
     */
    public function setApiUser( APIUser $apiUser )
    {
        $this->apiUser = $apiUser;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getAPIUser()
    {
        return $this->apiUser;
    }
}
