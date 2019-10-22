<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\VersionDiff;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\SPI\Comparison\ComparisonResult;

class FieldValueDiff extends ValueObject
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition */
    private $fieldDef;

    /** @var \eZ\Publish\SPI\Comparison\ComparisonResult */
    private $comparisonResult;

    /** @var bool */
    private $isChanged;

    public function __construct(
        FieldDefinition $fieldDef,
        ComparisonResult $comparisonResult,
        bool $isChanged
    ) {
        $this->fieldDef = $fieldDef;
        $this->comparisonResult = $comparisonResult;
        $this->isChanged = $isChanged;
    }

    public function getComparisonResult(): ComparisonResult
    {
        return $this->comparisonResult;
    }

    public function getFieldDef(): FieldDefinition
    {
        return $this->fieldDef;
    }

    public function isChanged(): bool
    {
        return $this->isChanged;
    }
}
