<?php

/**
 * File containing the Field sort clause visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\SortClauseVisitor\Field;

use eZ\Publish\Core\Search\Elasticsearch\Content\SortClauseVisitor\FieldBase;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Visits the Field sort clause.
 */
class Field extends FieldBase
{
    /**
     * Check if visitor is applicable to current sortClause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    public function canVisit(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\Field;
    }

    /**
     * Map field value to a proper Elasticsearch representation.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no sortable fields are found for the given sort clause target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return mixed
     */
    public function visit(SortClause $sortClause)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget $target */
        $target = $sortClause->targetData;
        $fieldName = $this->getSortFieldName(
            $sortClause,
            $target->typeIdentifier,
            $target->fieldIdentifier
        );

        if ($fieldName === null) {
            throw new InvalidArgumentException(
                '$sortClause->targetData',
                'No searchable fields found for the given sort clause target ' .
                "'{$target->fieldIdentifier}' on '{$target->typeIdentifier}'."
            );
        }

        return [
            "fields_doc.{$fieldName}" => [
                'nested_filter' => [
                    'term' => $this->getNestedFilterTerm(null),
                ],
                'order' => $this->getDirection($sortClause),
            ],
        ];
    }
}
