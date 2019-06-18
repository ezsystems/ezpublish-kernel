<?php

/**
 * File containing the RoleCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\User;

use eZ\Publish\SPI\Persistence\ValueObject;

class RoleCreateStruct extends ValueObject
{
    /**
     * Identifier of the role.
     *
     * Legacy note: Maps to name in 4.x.
     *
     * @var string
     */
    public $identifier;

    /**
     * Contains an array of role policies.
     *
     * @var mixed[]
     */
    public $policies = [];
}
