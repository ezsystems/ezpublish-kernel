<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the criterion tree into a Solr query
 */
abstract class CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion
     *
     * @param Criterion $criterion
     *
     * @return boolean
     */
    abstract public function canVisit( Criterion $criterion );

    /**
     * Map field value to a proper Solr representation
     *
     * @param Criterion $criterion
     * @param CriterionVisitor $subVisitor
     *
     * @return void
     */
    abstract public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null );

    /**
     * Get Solr range
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
     * @param mixed $operator
     * @param mixed $start
     * @param mixed $end
     *
     * @return void
     */
    protected function getRange( $operator, $start, $end )
    {
        $startBrace = '[';
        $startValue = '*';
        $endValue   = '*';
        $endBrace   = ']';

        switch ( $operator )
        {
            case Operator::GT:
                $startBrace = '{';
                $endBrace   = '}';
                // Intentionally omitted break

            case Operator::GTE:
                $startValue = $start;
                break;

            case Operator::LT:
                $startBrace = '{';
                $endBrace   = '}';
                // Intentionally omitted break

            case Operator::LTE:
                $endValue = $end;
                break;

            case Operator::BETWEEN:
                $startValue = $start;
                $endValue   = $end;
                break;

            default:
                throw new \RuntimeException( "Unknown operator: $operator" );
        }

        return "$startBrace$startValue TO $endValue$endBrace";
    }
}

