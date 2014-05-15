<?php
/**
 * File containing the MapLocationDistanceIn criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\MapLocation;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\MapLocation;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Visits the MapLocationDistance criterion
 */
class MapLocationDistanceIn extends MapLocation
{
    /**
     * Check if visitor is applicable to current criterion
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    public function canVisit( Criterion $criterion )
    {
        return
            $criterion instanceof Criterion\MapLocationDistance &&
            ( ( $criterion->operator ?: Operator::IN ) === Operator::IN ||
              $criterion->operator === Operator::EQ );
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue $location */
        $location = $criterion->valueData;
        $fieldTypes = $this->getFieldTypes( $criterion );
        $criterion->value = (array)$criterion->value;

        if ( !isset( $fieldTypes[$criterion->target][$this->typeName] ) &&
            !isset( $fieldTypes[$criterion->target]["custom"] ) )
        {
            throw new InvalidArgumentException(
                "\$criterion->target",
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        if ( isset( $fieldTypes[$criterion->target]["custom"] ) )
        {
            $names = $fieldTypes[$criterion->target]["custom"];
        }
        else
        {
            $names = $fieldTypes[$criterion->target][$this->typeName];
        }

        $queries = array();
        foreach ( $criterion->value as $value )
        {
            foreach ( $names as $name )
            {
                $queries[] = "geodist({$name},{$location->latitude},{$location->longitude}):{$value}";
            }
        }

        return '(' . implode( ' OR ', $queries ) . ')';
    }
}

