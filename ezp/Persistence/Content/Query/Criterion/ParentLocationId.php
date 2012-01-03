<?php
/**
 * File containing the ezp\Persistence\Content\Query\Criterion\ParentLocationId
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\Persistence\Content\Query\Criterion;
use ezp\Persistence\Content\Query\Criterion,
    ezp\Persistence\Content\Query\Criterion\Operator\Specifications,
    ezp\Persistence\Content\Query\CriterionInterface;

/**
 * A criterion that matches content based on its parent location id
 *
 * Own location id is done using {@see LocationId}
 *
 * Supported operators:
 * - IN: matches against a list of location ids
 * - EQ: matches against a unique location id
 */
class ParentLocationId extends Criterion implements CriterionInterface
{
    /**
     * Creates a new ParentLocationId criterion
     *
     * @param integer|array(integer) One or more locationId parent locations must be matched against
     *
     * @throw InvalidArgumentException if a non numeric id is given
     * @throw InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $value )
    {
        parent::__construct( null, null, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
        );
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $value );
    }
}
?>
