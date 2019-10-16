<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Compare;

use eZ\Publish\API\Repository\CompareEngine;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\CompareResult;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldType\NoCompareResult;
use eZ\Publish\SPI\Compare\CompareField;

final class NoCompareEngine implements CompareEngine
{
    public function compareFieldsData(CompareField $fieldA, CompareField $fieldB): CompareResult
    {
        return new NoCompareResult();
    }
}
