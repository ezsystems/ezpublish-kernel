<?php
/**
 * File containing the CustomFieldRange Field criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor\CustomField;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor\CustomField;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the CustomField criterion with range operators (LT, LTE, GT, GTE and BETWEEN)
 *
 * @todo needs tests
 */
class CustomFieldRange extends CustomField
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
            $criterion instanceof Criterion\CustomField &&
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
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return array
     */
    protected function getCondition( Criterion $criterion )
    {
        $start = $criterion->value[0];
        $end = isset( $criterion->value[1] ) ? $criterion->value[1] : null;
        $range = $this->getRange( $criterion->operator, $start, $end );

        return array(
            "range" => array(
                "fields_doc." . $criterion->target => $range,
            ),
        );
    }

    /**
     * Map field value to a proper Elasticsearch filter representation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher $dispatcher
     * @param array $fieldFilters
     *
     * @return mixed
     */
    public function visitFilter( Criterion $criterion, Dispatcher $dispatcher, array $fieldFilters )
    {
        $filter = array(
            "nested" => array(
                "path" => "fields_doc",
                "filter" => $this->getCondition( $criterion ),
            ),
        );

        $fieldFilter = $this->getFieldFilter( $fieldFilters );

        if ( $fieldFilter !== null )
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
}
