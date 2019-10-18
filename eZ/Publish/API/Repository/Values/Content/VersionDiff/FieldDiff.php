<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\VersionDiff;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ValueObject;

class FieldDiff extends ValueObject
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition */
    private $fieldDef;

    /** @var \eZ\Publish\API\Repository\Values\Content\VersionDiff\ComparisonResult */
    private $diffValue;

    public function __construct(
        FieldDefinition $fieldDef,
        ComparisonResult $diffValue
    ) {
        $this->fieldDef = $fieldDef;
        $this->diffValue = $diffValue;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\VersionDiff\ComparisonResult
     */
    public function getDiffValue(): ComparisonResult
    {
        return $this->diffValue;
    }

    public function getFieldDef(): FieldDefinition
    {
        return $this->fieldDef;
    }
}
