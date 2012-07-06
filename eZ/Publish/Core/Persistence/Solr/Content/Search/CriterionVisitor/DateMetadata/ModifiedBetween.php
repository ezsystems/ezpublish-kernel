<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\DateMetadata;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\DateMetadata,
    eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the DateMetadata criterion
 */
class ModifiedBetween extends DateMetadata
{
    /**
     * CHeck if visitor is applicable to current criterion
     *
     * @param Criterion $criterion
     * @return bool
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
     * @param DocumentField $field
     * @return void
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        $startBrace = '[';
        $startTime  = '*';
        $endTime    = '*';
        $endBrace   = ']';

        switch ( $criterion->operator )
        {
            case Operator::GT:
                $startBrace = '{';
                $endBrace   = '}';
                // Intentionally omitted break

            case Operator::GTE:
                $startTime = $this->getSolrTime( $criterion->value[0] );
                break;

            case Operator::LT:
                $startBrace = '{';
                $endBrace   = '}';
                // Intentionally omitted break

            case Operator::LTE:
                $endTime = $this->getSolrTime( $criterion->value[0] );
                break;

            case Operator::BETWEEN:
                $startTime = $this->getSolrTime( $criterion->value[0] );
                $endTime   = $this->getSolrTime( $criterion->value[1] );
                break;
        }

        return "modified_dt:$startBrace$startTime TO $endTime$endBrace";
    }
}

