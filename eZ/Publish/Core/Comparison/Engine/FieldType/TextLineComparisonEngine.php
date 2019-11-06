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

    /**
     * @param \eZ\Publish\SPI\Comparison\Field\TextLine $comparisonDataA
     * @param \eZ\Publish\SPI\Comparison\Field\TextLine $comparisonDataB
     */
    public function compareFieldsData(ComparisonData $comparisonDataA, ComparisonData $comparisonDataB): ComparisonResult
    {
        return new TextLineComparisonResult(
            $this->stringValueComparisonEngine->compareValues($comparisonDataA->textLine, $comparisonDataB->textLine)
        );
    }

    public function areEqual(ComparisonData $comparisonDataA, ComparisonData $comparisonDataB): bool
    {
        return $comparisonDataA->textLine->value === $comparisonDataB->textLine->value;
    }
}
