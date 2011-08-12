<?php
/**
 * File containing the SubTreeCriteria
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Criterion\Operator\Specifications,
    ezp\Persistence\Content\CriterionInterface;

/**
 * Criterion that matches content that belongs to a given list of SubtreeId
 *
 * Content will be matched if it is part of at least one of the given subtree id
 */
class SubtreeId extends Criterion implements CriterionInterface
{
    /**
     * Creates a new SubTree criterion
     *
     * @param integer|array(integer) $value an array of subtree ids
     *
     * @throws InvalidArgumentException if a non numeric id is given
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
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            )
        );
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $value );
    }
}
?>
