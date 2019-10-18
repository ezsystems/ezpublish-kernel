<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Comparison\Field;

use eZ\Publish\SPI\Comparison\ComparisonData;

class TextLine extends ComparisonData
{
    /** @var \eZ\Publish\SPI\Comparison\Value\StringComparisonValue */
    public $textLine;
}
