<?php
/**
 * File containing the range FieldRange Field criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor\Field;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use RuntimeException;

/**
 * Visits the Field criterion with range operators (LT, LTE, GT, GTE and BETWEEN)
 */
class FieldRange extends Field
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
            $criterion instanceof Criterion\Field &&
            (
                $criterion->operator === Operator::LT ||
                $criterion->operator === Operator::LTE ||
                $criterion->operator === Operator::GT ||
                $criterion->operator === Operator::GTE ||
                $criterion->operator === Operator::BETWEEN
            );
    }

    /**
     * Returns nested condition common for filter and query contexts.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return array
     */
    protected function getCondition( Criterion $criterion )
    {
        $fieldNames = $this->getFieldNames( $criterion, $criterion->target );
        $value = (array)$criterion->value;

        if ( empty( $fieldNames ) )
        {
            throw new InvalidArgumentException(
                "\$criterion->target",
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        $start = $value[0];
        $end = isset( $value[1] ) ? $value[1] : null;
        $range = $this->getRange( $criterion->operator, $start, $end );

        $ranges = array();
        foreach ( $fieldNames as $name )
        {
            $ranges[] = array(
                "range" => array(
                    "fields_doc." . $name => $range,
                ),
            );
        }

        return $ranges;
    }

    /**
     * Map field value to a proper Elasticsearch filter representation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $fieldFilters
     *
     * @return mixed
     */
    public function visitFilter( Criterion $criterion, Dispatcher $dispatcher, array $fieldFilters )
    {
        $filter = array(
            "nested" => array(
                "path" => "fields_doc",
                "filter" => array(
                    "or" => $this->getCondition( $criterion ),
                ),
            ),
        );

        $fieldFilter = $this->getFieldFilter( $fieldFilters );

        if ( $fieldFilters !== null )
        {
            $filter["nested"]["filter"] = array(
                "and" => array(
                    $fieldFilter,
                    $filter["nested"]["filter"],
                ),
            );
        }

        return $filter;
    }

    /**
     * Map field value to a proper Elasticsearch query representation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $fieldFilters
     *
     * @return mixed
     */
    public function visitQuery( Criterion $criterion, Dispatcher $dispatcher, array $fieldFilters )
    {
        $query = array(
            "bool" => array(
                "should" => $this->getCondition( $criterion ),
                "minimum_should_match" => 1,
            ),
        );

        $fieldFilter = $this->getFieldFilter( $fieldFilters );

        if ( $fieldFilter === null )
        {
            $query = array(
                "nested" => array(
                    "path" => "fields_doc",
                    "query" => $query,
                ),
            );
        }
        else
        {
            $query = array(
                "nested" => array(
                    "path" => "fields_doc",
                    "query" => array(
                        "filtered" => array(
                            "query" => $query,
                            "filter" => $fieldFilter,
                        ),
                    ),
                ),
            );
        }

        return $query;
    }
}
