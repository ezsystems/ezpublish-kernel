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
    ezp\Persistence\Content\Interfaces\Criterion as CriterionInterface;

/**
 * Criterion that matches content against a subtree.
 * Content will be matched if it is part of at least one of the given subtree id
 *
 */
class Subtree extends Criterion implements CriterionInterface
{
    /**
     * Creates a new SubTree criterion
     *
     * @param string $target Not used
     * @param string $operator
     *        Possible values:
     *        - Operator::IN, requires an array of subtree id as the $value
     *        - Operator::EQ, requires a single subtree id as the $value
     * @param array(integer) $value an array of subtree ids
     *
     * @throws InvalidArgumentException if a non numeric id is given
     * @throw InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $target, $operator, $value )
    {
        parent::__construct( $target, $operator, $value );
    }

    public function getSpecifications()
    {
        return array(
            new OperatorSpecifications(
                Operator::EQ,
                OperatorSpecifications::FORMAT_SINGLE,
                OperatorSpecifications::TYPE_INTEGER | OperatorSpecifications::TYPE_STRING
            ),
            new OperatorSpecifications(
                Operator::IN,
                OperatorSpecifications::FORMAT_ARRAY,
                OperatorSpecifications::TYPE_INTEGER | OperatorSpecifications::TYPE_STRING
            )
        );
    }
}
?>
