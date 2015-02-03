<?php
/**
 * File containing the MapLocationDistanceRange criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\CriterionVisitor\MapLocation;

use eZ\Publish\Core\Search\Solr\Content\CriterionVisitor\MapLocation;
use eZ\Publish\Core\Search\Solr\Content\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Visits the MapLocationDistance criterion
 */
class MapLocationDistanceRange extends MapLocation
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
            ( $criterion->operator === Operator::LT ||
              $criterion->operator === Operator::LTE ||
              $criterion->operator === Operator::GT ||
              $criterion->operator === Operator::GTE ||
              $criterion->operator === Operator::BETWEEN );
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Solr\Content\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        $criterion->value = (array)$criterion->value;

        $start = $criterion->value[0];
        $end = isset( $criterion->value[1] ) ? $criterion->value[1] : 63510;

        if ( ( $criterion->operator === Operator::LT ) ||
            ( $criterion->operator === Operator::LTE ) )
        {
            $end = $start;
            $start = null;
        }

        $fieldNames = $this->getFieldNames(
            $criterion,
            $criterion->target,
            $this->fieldTypeIdentifier,
            $this->fieldName
        );

        if ( empty( $fieldNames ) )
        {
            throw new InvalidArgumentException(
                "\$criterion->target",
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue $location */
        $location = $criterion->valueData;

        $queries = array();
        foreach ( $fieldNames as $name )
        {
            // @todo in future it should become possible to specify ranges directly on the filter (donut shape)
            $query = "{!geofilt sfield={$name} pt={$location->latitude},{$location->longitude} d={$end}}";
            if ( $start !== null )
            {
                $query = "{!frange l={$start}}{$query}";
            }

            // @todo: fix for SOLR version < 4.1.0, see https://issues.apache.org/jira/browse/SOLR-4093
            $queries[] = '_query_:"' . $query . '"';
        }

        return '(' . implode( ' OR ', $queries ) . ')';
    }
}
