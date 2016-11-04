<?php

/**
 * File containing the CreatedObjectStateGroup class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created object state group.
 */
class CreatedObjectStateGroup extends ValueObject
{
    /**
     * The created object state group.
     *
     * @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public $objectStateGroup;
}
