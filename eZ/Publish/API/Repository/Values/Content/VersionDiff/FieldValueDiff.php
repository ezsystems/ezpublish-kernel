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
    private $fieldDefinition;

    /** @var \eZ\Publish\SPI\Comparison\ComparisonResult */
    private $comparisonResult;

    /** @var bool */
    private $isChanged;

    public function __construct(
        FieldDefinition $fieldDefinition,
        ComparisonResult $comparisonResult,
        bool $isChanged
    ) {
        $this->fieldDefinition = $fieldDefinition;
        $this->comparisonResult = $comparisonResult;
        $this->isChanged = $isChanged;
    }

    public function getComparisonResult(): ComparisonResult
    {
        return $this->comparisonResult;
    }

    public function getFieldDefinition(): FieldDefinition
    {
        return $this->fieldDefinition;
    }

    public function isChanged(): bool
    {
        return $this->isChanged;
    }
}
