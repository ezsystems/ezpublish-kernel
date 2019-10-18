<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Comparison;

use eZ\Publish\SPI\Persistence\ValueObject;

abstract class ComparisonData extends ValueObject
{
    public function getType(): string
    {
        return static::class;
    }
}
