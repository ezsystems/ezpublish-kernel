<?php
/**
 * File containing the MatchNone criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Common\CriterionVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the MatchNone criterion
 */
class MatchNone extends CriterionVisitor
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
        return $criterion instanceof Criterion\MatchNone;
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
        return array(
            "terms" => array(
                "_id" => array(),
            ),
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
        return array(
            "terms" => array(
                "_id" => array(),
                "minimum_should_match" => 1,
            ),
        );
    }
}
