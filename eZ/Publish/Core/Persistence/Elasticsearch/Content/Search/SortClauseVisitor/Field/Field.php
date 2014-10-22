<?php
/**
 * File containing the Field sort clause visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor\Field;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor\FieldBase;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use RuntimeException;

/**
 * Visits the Field sort clause
 */
class Field extends FieldBase
{
    /**
     * Check if visitor is applicable to current sortClause
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return boolean
     */
    public function canVisit( SortClause $sortClause )
    {
        return $sortClause instanceof SortClause\Field;
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return mixed
     */
    public function visit( SortClause $sortClause )
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget $target */
        $target = $sortClause->targetData;
        $fieldName = $this->getSortFieldName(
            $sortClause,
            $target->typeIdentifier,
            $target->fieldIdentifier
        );

        if ( $fieldName === null )
        {
            throw new RuntimeException( "No sortable fields found" );
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget $target */
        $target = $sortClause->targetData;

        return array(
            "fields_doc.{$fieldName}" => array(
                "nested_filter" => array(
                    "term" => $this->getNestedFilterTerm( $target->languageCode ),
                ),
                "order" => $this->getDirection( $sortClause ),
            ),
        );
    }
}
