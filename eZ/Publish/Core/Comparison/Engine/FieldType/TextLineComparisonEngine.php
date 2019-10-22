<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Comparison\Engine\FieldType;

use eZ\Publish\Core\Comparison\Engine\Value\StringValueComparisonEngine;
use eZ\Publish\SPI\Comparison\ComparisonData;
use eZ\Publish\SPI\Comparison\ComparisonEngine;
use eZ\Publish\SPI\Comparison\ComparisonResult;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldType\TextLineComparisonResult;

final class TextLineComparisonEngine implements ComparisonEngine
{
    /** @var \eZ\Publish\Core\Comparison\Engine\Value\StringValueComparisonEngine */
    private $stringValueComparisonEngine;

    public function __construct(StringValueComparisonEngine $stringValueComparisonEngine)
    {
        $this->stringValueComparisonEngine = $stringValueComparisonEngine;
    }

    public function compareFieldsData(ComparisonData $fieldA, ComparisonData $fieldB): ComparisonResult
    {
        /** @var \eZ\Publish\SPI\Comparison\Field\TextLine $fieldA */
        /** @var \eZ\Publish\SPI\Comparison\Field\TextLine $fieldB */
        return new TextLineComparisonResult(
            $this->stringValueComparisonEngine->compareValues($fieldA->textLine, $fieldB->textLine)
        );
    }
}
