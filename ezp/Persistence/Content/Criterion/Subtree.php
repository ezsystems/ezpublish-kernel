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
    ezp\Persistence\Content\Interfaces\Criterion as CriterionInterface,
    InvalidArgumentException;

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
     * @param array(integer) $subtreeId an array of subtree ids
     *
     * @throws InvalidArgumentException if a non numeric id is given
     * @throw InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $target = null, $operator, $subtreeId )
    {
        if ( $operator != Operator::IN )
        {
            if ( !is_array( $value ) )
            {
                throw new InvalidArgumentException( "Operator::IN requires an array of values" );
            }
            foreach ( $subtreeId as $id )
            {
                if ( !is_numeric( $id ) )
                {
                    throw new InvalidArgumentException( "Only numeric ids are accepted" );
                }
            }
        }
        // single value, EQ operator
        elseif ( $operator == Operator::EQ )
        {
            if ( is_array( $value ) )
            {
                throw new InvalidArgumentException( "Operator::EQ requires a single value" );
            }
            if ( !is_numeric( $value ) )
            {
                throw new InvalidArgumentException( "Only numeric ids are accepted" );
            }
        }
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * The subtree value used by the criterion.
     *
     * A single value if $operator is Operator::EQ
     * An array of values if $operator is Operator::IN
     *
     * @var integer|array(integer)
     */
    public $value;
}
?>
