<?php
/**
 * File containing the SortClauseVisitor\MapLocationDistance class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor\Field;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor\FieldBase;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use RuntimeException;

/**
 * Visits the MapLocationDistance sort clause
 */
class MapLocationDistance extends FieldBase
{
    /**
     * Name of the field type that sort clause can handle
     *
     * @var string
     */
    protected $typeName = "ez_geolocation";

    /**
     * Check if visitor is applicable to current sortClause
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return boolean
     */
    public function canVisit( SortClause $sortClause )
    {
        return $sortClause instanceof SortClause\MapLocationDistance;
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no sortable fields are found for the given sort clause target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return mixed
     */
    public function visit( SortClause $sortClause )
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\MapLocationTarget $target */
        $target = $sortClause->targetData;
        $types = $this->getFieldTypes(
            $target->typeIdentifier,
            $target->fieldIdentifier,
            $target->languageCode
        );

        if ( empty( $types ) || !isset( $types["ez_geolocation"] ) )
        {
            throw new RuntimeException( "No sortable fields found" );
        }

        $fieldName = $types["ez_geolocation"];

        return array(
            "_geo_distance" => array(
                "nested_path" => "fields_doc",
                "nested_filter" => array(
                    "term" => $this->getNestedFilterTerm( $target->languageCode ),
                ),
                "order" => $this->getDirection( $sortClause ),
                "fields_doc.{$fieldName}" => array(
                    "lat" => $target->latitude,
                    "lon" => $target->longitude,
                ),
                "unit" => "km",
            ),
        );
    }
}
