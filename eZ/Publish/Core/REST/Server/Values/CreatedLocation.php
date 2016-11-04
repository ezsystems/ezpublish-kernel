<?php

/**
 * File containing the CreatedLocation class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created location.
 */
class CreatedLocation extends ValueObject
{
    /**
     * The created location.
     *
     * @var \eZ\Publish\Core\REST\Server\Values\RestLocation
     */
    public $restLocation;
}
