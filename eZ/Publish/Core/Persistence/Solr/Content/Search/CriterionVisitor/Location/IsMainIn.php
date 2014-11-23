<?php
/**
 * File containing Solr Criterion Visitor
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\Location;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the Location\IsMainLocation criterion
 */
class IsMainIn extends CriterionVisitor
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
            $criterion instanceof Criterion\Location\IsMainLocation &&
                        ( ( $criterion->operator ?: Operator::IN ) === Operator::IN ||
                          $criterion->operator === Operator::EQ );
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param Criterion $criterion
     * @param CriterionVisitor $subVisitor
     * @param bool $isChildQuery
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null, $isChildQuery = false )
    {
        return '(' .
            implode(
                ' OR ',
                array_map(
                    function ( $value )
                    {
                        return 'is_main_b:"' . ( Criterion\Location\IsMainLocation::NOT_MAIN === $value ? 0 : 1 ) . '"';
                    },
                    $criterion->value
                )
            ) .
            ')';
    }
}

