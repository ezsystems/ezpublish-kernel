<?php

/**
 * File containing the CreatedUserGroup class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created UserGroup.
 */
class CreatedUserGroup extends ValueObject
{
    /**
     * The created user group.
     *
     * @var \eZ\Publish\Core\REST\Server\Values\RestUserGroup
     */
    public $userGroup;
}
