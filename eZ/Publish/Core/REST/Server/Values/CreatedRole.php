<?php

/**
 * File containing the CreatedRole class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created Role.
 */
class CreatedRole extends ValueObject
{
    /**
     * The created role.
     *
     * @var \eZ\Publish\Core\REST\Server\Values\RestRole
     */
    public $role;
}
