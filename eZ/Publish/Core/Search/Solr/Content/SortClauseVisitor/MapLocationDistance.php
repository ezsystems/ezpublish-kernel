<?php

/**
 * File containing the SortClauseVisitor\MapLocationDistance class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\SortClauseVisitor;

use eZ\Publish\Core\Search\Solr\Content\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Visits the sortClause tree into a Solr query.
 */
class MapLocationDistance extends SortClauseVisitor
{
    /**
     * Name of the field type's indexed field that criterion can handle.
     *
     * @var string
     */
    protected $fieldName;

    /**
     * Field name resolver.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldNameResolver
     */
    protected $fieldNameResolver;

    /**
     * Create from field name resolver and field name.
     *
     * @param \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver
     * @param string $fieldName
     */
    public function __construct(FieldNameResolver $fieldNameResolver, $fieldName)
    {
        $this->fieldNameResolver = $fieldNameResolver;
        $this->fieldName = $fieldName;
    }

    /**
     * Get sort field name.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param string $contentTypeIdentifier
     * @param string $fieldDefinitionIdentifier
     * @param string $name
     *
     * @return array
     */
    protected function getSortFieldName(
        SortClause $sortClause,
        $contentTypeIdentifier,
        $fieldDefinitionIdentifier,
        $name = null
    ) {
        return $this->fieldNameResolver->getSortFieldName(
            $sortClause,
            $contentTypeIdentifier,
            $fieldDefinitionIdentifier,
            $name
        );
    }

    /**
     * CHeck if visitor is applicable to current sortClause.
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
     * Map field value to a proper Solr representation.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no sortable fields are found for the given sort clause target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return string
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
                "\$sortClause->targetData",
                'No searchable fields found for the given sort clause target ' .
                "'{$target->fieldIdentifier}' on '{$target->typeIdentifier}'."
            );
        }

        return "geodist({$fieldName},{$target->latitude},{$target->longitude})" . $this->getDirection($sortClause);
    }
}
