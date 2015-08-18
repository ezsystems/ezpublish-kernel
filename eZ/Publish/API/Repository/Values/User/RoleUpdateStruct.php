<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\RoleUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to update a role.
 */
class RoleUpdateStruct extends ValueObject
{
    /**
     * Readable string identifier of a role.
     *
     * @var string
     */
    public $identifier;

    /**
     * The status of the role.
     *
     * @var int One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     */
    public $status;
}
