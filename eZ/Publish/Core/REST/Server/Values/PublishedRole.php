<?php

/**
 * File containing the PublishedRole class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a published Role.
 */
class PublishedRole extends ValueObject
{
    /**
     * The published role.
     *
     * @var \eZ\Publish\Core\REST\Server\Values\RestRole
     */
    public $role;
}
