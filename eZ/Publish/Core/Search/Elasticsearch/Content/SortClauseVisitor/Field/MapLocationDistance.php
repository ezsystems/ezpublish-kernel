<?php

/**
 * File containing the SortClauseVisitor\MapLocationDistance class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\SortClauseVisitor\Field;

use eZ\Publish\Core\Search\Elasticsearch\Content\SortClauseVisitor\FieldBase;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Search\Common\FieldNameResolver;

/**
 * Visits the MapLocationDistance sort clause.
 */
class MapLocationDistance extends FieldBase
{
    /**
     * Name of the field type's indexed field that criterion can handle.
     *
     * @var string
     */
    protected $fieldName;

    /**
     * @param \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver
     * @param string $fieldName
     */
    public function __construct(FieldNameResolver $fieldNameResolver, $fieldName)
    {
        $this->fieldName = $fieldName;

        parent::__construct($fieldNameResolver);
    }

    /**
     * Check if visitor is applicable to current sortClause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    public function canVisit(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\MapLocationDistance;
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
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\MapLocationTarget $target */
        $target = $sortClause->targetData;
        $fieldName = $this->getSortFieldName(
            $sortClause,
            $target->typeIdentifier,
            $target->fieldIdentifier,
            $this->fieldName
        );

        if ($fieldName === null) {
            throw new InvalidArgumentException(
                '$sortClause->targetData',
                'No searchable fields found for the given sort clause target ' .
                "'{$target->fieldIdentifier}' on '{$target->typeIdentifier}'."
            );
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\MapLocationTarget $target */
        $target = $sortClause->targetData;

        return [
            '_geo_distance' => [
                'nested_path' => 'fields_doc',
                'nested_filter' => [
                    'term' => $this->getNestedFilterTerm(null),
                ],
                'order' => $this->getDirection($sortClause),
                "fields_doc.{$fieldName}" => [
                    'lat' => $target->latitude,
                    'lon' => $target->longitude,
                ],
                'unit' => 'km',
            ],
        ];
    }
}
