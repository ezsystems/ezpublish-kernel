<?php
/**
 * File containing the LogicalNot criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Common\CriterionVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use RuntimeException;

/**
 * Visits the LogicalNot criterion
 */
class LogicalNot extends CriterionVisitor
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
        return $criterion instanceof Criterion\LogicalNot;
    }

    /**
     * Validates criterion
     *
     * @throws \RuntimeException
     *
     * @param Criterion $criterion
     */
    protected function validateCriterionInput( Criterion $criterion )
    {
        /** @var $criterion \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator */
        if ( !isset( $criterion->criteria[0] ) || ( count( $criterion->criteria ) > 1 ) )
        {
            throw new RuntimeException( "Invalid aggregation in LogicalNot criterion." );
        }
    }

    /**
     * Map field value to a proper Elasticsearch filter representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher $dispatcher
     *
     * @return string
     */
    public function visitFilter( Criterion $criterion, Dispatcher $dispatcher = null )
    {
        $this->validateCriterionInput( $criterion );

        /** @var $criterion \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator */
        return array(
            "not" => $dispatcher->dispatch( $criterion->criteria[0], Dispatcher::CONTEXT_FILTER )
        );
    }

    /**
     * Map field value to a proper Elasticsearch query representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher $dispatcher
     *
     * @return string
     */
    public function visitQuery( Criterion $criterion, Dispatcher $dispatcher = null )
    {
        $this->validateCriterionInput( $criterion );

        /** @var $criterion \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator */
        return array(
            "bool" => array(
                "must_not" => $dispatcher->dispatch( $criterion->criteria[0], Dispatcher::CONTEXT_FILTER ),
            ),
        );
    }
}
