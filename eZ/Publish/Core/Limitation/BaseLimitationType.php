<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;

/**
 * Base class for limitation types.
 */
abstract class BaseLimitationType implements SPILimitationTypeInterface
{
    public function evaluateSingle(Limitation $limitation, $value)
    {
        return in_array($value, $limitation->limitationValues);
    }
}
