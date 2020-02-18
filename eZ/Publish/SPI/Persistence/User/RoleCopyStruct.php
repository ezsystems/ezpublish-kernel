<?php

/**
 * File containing the RoleCopyStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\User;

use eZ\Publish\SPI\Persistence\ValueObject;

class RoleCopyStruct extends ValueObject
{
    /**
     * ID of user role being cloned.
     *
     * @var mixed
     */
    public $clonedId;

    /**
     * Identifier of new role.
     *
     * @var string
     */
    public $newIdentifier;

    /**
     * Contains an array of role policies.
     *
     * @var mixed[]
     */
    public $policies = [];
}
