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

/**
 * Criterion that matches content against a subtree.
 * Content will be matched if it is part of at least one of the given subtree id
 *
 * @package ezp.persistence.content.criteria
 */
class SubTree extends Criterion implements \ezp\Persistence\Content\Interfaces\Criterion
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
     */
    public function __construct( $target = null, $operator, $subtreeId )
    {
        if ( is_array( $subtreeId ) )
        {
            if ( $operator != Operator::IN )
            {
                throw new \InvalidArgumentException( "An array of subtree ids requires the IN operator" );
            }
            foreach( $subtreeId as $id )
            {
                if ( !is_numeric( $id ) )
                {
                    throw new \InvalidArgumentException( "Only numeric subtree ids are accepted" );
                }
            }
        }
        // single value, EQ operator
        else
        {
            if ( $operator != Operator::EQ )
            {
                throw new \InvalidArgumentException( "A single subtree id requires the EQ operator" );
            }
            if ( !is_numeric( $value ) )
            {
                throw new \InvalidArgumentException( "Only numeric subtree ids are accepted" );
            }
        }
        $this->operator = $operator;
        $this->subtreeList = $subtreeId;
    }

    /**
     * The subtree value used by the criterion.
     *
     * A single value if $operator is Operator::EQ
     * An array of values if $operator is Operator::IN
     *
     * @var integer|array(integer)
     */
    public $subtreeList;
}
?>
