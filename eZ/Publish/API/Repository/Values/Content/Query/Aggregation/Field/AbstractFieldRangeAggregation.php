<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\FieldAggregation;

abstract class AbstractFieldRangeAggregation extends AbstractRangeAggregation implements FieldAggregation
{
    use FieldAggregationTrait;

    public function __construct(
        string $name,
        string $contentTypeIdentifier,
        string $fieldDefinitionIdentifier,
        array $ranges = []
    ) {
        parent::__construct($name, $ranges);

        $this->contentTypeIdentifier = $contentTypeIdentifier;
        $this->fieldDefinitionIdentifier = $fieldDefinitionIdentifier;
    }
}
