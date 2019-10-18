<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Comparison\Value;

use eZ\Publish\SPI\Comparison\ComparisonValue;

class StringComparisonValue extends ComparisonValue
{
    /** @var string|null */
    public $value;
}
