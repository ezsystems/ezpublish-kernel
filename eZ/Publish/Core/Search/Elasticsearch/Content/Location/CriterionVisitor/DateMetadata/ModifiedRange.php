<?php
/**
 * File containing the ModifiedRange criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Elasticsearch\Content\Location\CriterionVisitor\DateMetadata;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor\DateMetadata;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the ModifiedRange DateMetadata criterion
 */
class ModifiedRange extends DateMetadata
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
            $criterion instanceof Criterion\DateMetadata &&
            $criterion->target === "modified" &&
            (
                $criterion->operator === Operator::LT ||
                $criterion->operator === Operator::LTE ||
                $criterion->operator === Operator::GT ||
                $criterion->operator === Operator::GTE ||
                $criterion->operator === Operator::BETWEEN
            );
    }

    /**
     * Map field value to a proper Elasticsearch filter representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $fieldFilters
     *
     * @return mixed
     */
    public function visitFilter( Criterion $criterion, Dispatcher $dispatcher, array $fieldFilters )
    {
        $start = $this->getNativeTime( $criterion->value[0] );
        $end = isset( $criterion->value[1] ) ? $this->getNativeTime( $criterion->value[1] ) : null;

        return array(
            "range" => array(
                "content_modified_dt" => $this->getRange( $criterion->operator, $start, $end ),
            ),
        );
    }
}
