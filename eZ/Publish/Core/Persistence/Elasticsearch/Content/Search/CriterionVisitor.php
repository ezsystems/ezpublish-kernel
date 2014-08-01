<?php
/**
 * File containing the base CriterionVisitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use RuntimeException;

/**
 * Visits the criterion tree into a Elasticsearch query
 */
abstract class CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    abstract public function canVisit( Criterion $criterion );

    /**
     * Map field value to a proper Solr representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor $subVisitor
     *
     * @return string
     */
    abstract public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null );

    /**
     * Get Elasticsearch range filter
     *
     * Start and end are optional, depending on the respective operator. Pass
     * null in this case. The operator may be one of:
     *
     * - case Operator::GT:
     * - case Operator::GTE:
     * - case Operator::LT:
     * - case Operator::LTE:
     * - case Operator::BETWEEN:
     *
     * @throws \RuntimeException If operator is no recognized
     *
     * @param mixed $operator
     * @param mixed $start
     * @param mixed $end
     *
     * @return string
     */
    protected function getRange( $operator, $start, $end )
    {
        if ( ( $operator === Operator::LT ) || ( $operator === Operator::LTE ) )
        {
            $end = $start;
            $start = null;
        }

        switch ( $operator )
        {
            case Operator::GT:
                $range = array(
                    "gt" => $start,
                );
                break;

            case Operator::GTE:
                $range = array(
                    "gte" => $start,
                );
                break;

            case Operator::LT:
                $range = array(
                    "lt" => $end,
                );
                break;

            case Operator::LTE:
                $range = array(
                    "lte" => $end,
                );
                break;

            case Operator::BETWEEN:
                $range = array(
                    "gte" => $start,
                    "lte" => $end,
                );
                break;

            default:
                throw new RuntimeException( "Unknown operator '{$operator}'" );
        }

        return $range;
    }
}

