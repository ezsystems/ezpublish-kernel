<?php
/**
 * File containing the SortClauseVisitor\MapLocationDistance class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor;

use eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldMap;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Visits the sortClause tree into a Solr query
 */
class MapLocationDistance extends SortClauseVisitor
{
    /**
     * Field map
     *
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldMap
     */
    protected $fieldMap;

    /**
     * Name of the field type that sort clause can handle
     *
     * @var string
     */
    protected $typeName = "ez_geolocation";

    /**
     * Create from content type handler and field registry
     *
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldMap $fieldMap
     */
    public function __construct( FieldMap $fieldMap )
    {
        $this->fieldMap = $fieldMap;
    }

    /**
     * Get field type information
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface $sortClause
     *
     * @return array
     */
    protected function getFieldTypes( CustomFieldInterface $sortClause )
    {
        return $this->fieldMap->getFieldTypes( $sortClause );
    }

    /**
     * CHeck if visitor is applicable to current sortClause
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
     * Map field value to a proper Solr representation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no sortable fields are found for the given sort clause target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return string
     */
    public function visit( SortClause $sortClause )
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface $sortClause */
        $fieldTypes = $this->getFieldTypes( $sortClause );
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause */
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\MapLocationTarget $target */
        $target = $sortClause->targetData;

        if ( !isset( $fieldTypes[$target->fieldIdentifier][$this->typeName] ) &&
            !isset( $fieldTypes[$target->fieldIdentifier]["custom"] ) )
        {
            throw new InvalidArgumentException(
                "\$sortClause->targetData->fieldIdentifier",
                "No searchable fields found for the given sort clause target " .
                "field identifier '{$target->fieldIdentifier}'."
            );
        }

        if ( isset( $fieldTypes[$target->fieldIdentifier]["custom"] ) )
        {
            $names = $fieldTypes[$target->fieldIdentifier]["custom"];
        }
        else
        {
            $names = $fieldTypes[$target->fieldIdentifier][$this->typeName];
        }

        $sortClauses = array();
        foreach ( $names as $name )
        {
            $sortClauses[] = "geodist({$name},{$target->latitude},{$target->longitude})" . $this->getDirection( $sortClause );
        }

        return implode( ', ', $sortClauses );
    }
}
