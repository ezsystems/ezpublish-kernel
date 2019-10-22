<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Comparison\Engine;

use eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldType\NoComparisonResult;
use eZ\Publish\SPI\Comparison\ComparisonData;
use eZ\Publish\SPI\Comparison\ComparisonEngine;
use eZ\Publish\SPI\Comparison\ComparisonResult;

final class NoComparisonValueEngine implements ComparisonEngine
{
    public function compareFieldsData(ComparisonData $fieldA, ComparisonData $fieldB): ComparisonResult
    {
        return new NoComparisonResult();
    }
}
