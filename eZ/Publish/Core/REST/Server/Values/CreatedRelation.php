<?php

/**
 * File containing the CreatedRelation class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created relation.
 */
class CreatedRelation extends ValueObject
{
    /**
     * The created relation.
     *
     * @var \eZ\Publish\Core\REST\Server\Values\RestRelation
     */
    public $relation;
}
