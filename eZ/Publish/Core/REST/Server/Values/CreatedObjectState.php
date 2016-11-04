<?php

/**
 * File containing the CreatedObjectState class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created object state.
 */
class CreatedObjectState extends ValueObject
{
    /**
     * The created object state.
     *
     * @var \eZ\Publish\Core\REST\Common\Values\RestObjectState
     */
    public $objectState;
}
