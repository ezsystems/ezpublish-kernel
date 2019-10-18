<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType;

use eZ\Publish\SPI\Comparison\ComparisonData;
use eZ\Publish\SPI\Comparison\Field\NoComparison;
use eZ\Publish\SPI\FieldType\Comparable;
use eZ\Publish\SPI\Persistence\Content\FieldValue;

class NonComparable implements Comparable
{
    public const FIELD_TYPE_ALIAS = 'eznoncomparable';

    public function getDataToCompare(FieldValue $value): ComparisonData
    {
        return new NoComparison();
    }
}
