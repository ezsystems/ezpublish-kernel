<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\User;

use eZ\Publish\SPI\Persistence\ValueObject;

class RoleCopyStruct extends ValueObject
{
    /**
     * ID of user role being cloned.
     *
     * @var int
     */
    public $clonedId;

    /**
     * Identifier of new role.
     *
     * @var string
     */
    public $newIdentifier;

    /**
     * Status of new role.
     *
     * @var string
     */
    public $status;

    /**
     * Contains an array of role policies.
     *
     * @var array
     */
    public $policies = [];
}
