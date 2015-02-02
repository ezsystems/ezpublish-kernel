<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\DateMetadata;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\DateMetadata;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the DateMetadata criterion
 */
class ModifiedBetween extends DateMetadata
{
    /**
     * CHeck if visitor is applicable to current criterion
     *
     * @param Criterion $criterion
     *
     * @return boolean
     */
    public function canVisit( Criterion $criterion )
    {
        return
            $criterion instanceof Criterion\DateMetadata &&
            $criterion->target === "modified" &&
            ( $criterion->operator === Operator::LT ||
              $criterion->operator === Operator::LTE ||
              $criterion->operator === Operator::GT ||
              $criterion->operator === Operator::GTE ||
              $criterion->operator === Operator::BETWEEN );
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param Criterion $criterion
     * @param CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        $start = $this->getSolrTime( $criterion->value[0] );
        $end   = isset( $criterion->value[1] ) ? $this->getSolrTime( $criterion->value[1] ) : null;

        if ( ( $criterion->operator === Operator::LT ) ||
             ( $criterion->operator === Operator::LTE ) )
        {
            $end = $start;
            $start = null;
        }

        return "modified_dt:" . $this->getRange( $criterion->operator, $start, $end );
    }
}

