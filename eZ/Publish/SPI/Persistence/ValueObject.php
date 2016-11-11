<?php

/**
 * File containing the ValueObject class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence;

use eZ\Publish\API\Repository\Values\ValueObject as APIValueObject;

/**
 * Base SPI Value object.
 *
 * All properties of SPI\ValueObject *must* be serializable for cache & NoSQL use.
 */
abstract class ValueObject extends APIValueObject
{
}
