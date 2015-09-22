<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\User\User class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;

/**
 * This class represents a user reference for use in sessions and Repository.
 */
class UserReference implements APIUserReference
{
    /**
     * @var mixed
     */
    private $userId;

    /**
     * @param mixed $userId
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * The User id of the User this reference represent.
     *
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
