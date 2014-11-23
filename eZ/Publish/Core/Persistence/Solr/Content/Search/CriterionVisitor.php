<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
     * @param bool $isChildQuery
     *
     * @return string
     */
    abstract public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null, $isChildQuery = false );

    /**
     * @param bool $isChildQuery
     * @return string
     */
    protected function getParentJoinString( $isChildQuery )
    {
        if ( !$isChildQuery )
            return '{!parent which="doc_type_id:content"}';

        return '';
    }

    /**
     * @param bool $isChildQuery
     * @return string
     */
    protected function getChildJoinString( $isChildQuery )
    {
        if ( $isChildQuery )
            return '{!child of="doc_type_id:content"}';

        return '';
    }

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
     * @return string
     */
    protected function getFRange( $operator, $start, $end )
    {
        $l = '';
        $u = '';
        $incl   = ' incl=true';
        $incu   = ' incu=true';

        switch ( $operator )
        {
            case Operator::GT:
                $incl = ' incl=false';
                // Intentionally omitted break

            case Operator::GTE:
                $l = ' l=' . $start;
                break;

            case Operator::LT:
                $incu = ' incu=false';
                // Intentionally omitted break

            case Operator::LTE:
                $u = ' u=' . $end;
                break;

            case Operator::BETWEEN:
                $l = ' l=' . $start;
                $u = ' u=' . $end;
                break;

            default:
                throw new \RuntimeException( "Unknown operator: $operator" );
        }

        return "{!frange{$l}{$u}{$incl}{$incu}}";
    }
}

