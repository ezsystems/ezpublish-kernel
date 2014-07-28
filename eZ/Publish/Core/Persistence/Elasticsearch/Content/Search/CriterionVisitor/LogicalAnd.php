<?php
/**
 * File containing the LogicalAnd criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the LogicalAnd criterion
 */
class LogicalAnd extends CriterionVisitor
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
        return $criterion instanceof Criterion\LogicalAnd;
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        /** @var $criterion \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator */
        return array(
            "and" => array_map(
                function ( $value ) use ( $subVisitor )
                {
                    return $subVisitor->visit( $value );
                },
                $criterion->criteria
            )
        );
    }
}
