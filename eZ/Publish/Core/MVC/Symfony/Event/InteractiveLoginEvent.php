<?php

/**
 * File containing the InteractiveLoginEvent class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Event;

use eZ\Publish\API\Repository\Values\User\User as APIUser;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent as BaseInteractiveLoginEvent;

class InteractiveLoginEvent extends BaseInteractiveLoginEvent
{
    /** @var \eZ\Publish\API\Repository\Values\User\User */
    private $apiUser;

    /**
     * Checks if an API user has been provided.
     *
     * @return bool
     */
    public function hasAPIUser()
    {
        return isset($this->apiUser);
    }

    /**
     * Injects an API user to be injected in the repository.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser
     */
    public function setApiUser(APIUser $apiUser)
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
