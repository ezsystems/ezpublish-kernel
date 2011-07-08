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
class SubTree extends Criterion
{
    /**
     * Creates a new SubTree criterion
     *
     * @param integer|array(integer) $subtreeId
     * @throws InvalidArgumentException if a non numeric id is given
     */
    public function __construct( $subtreeId )
    {
        if ( !is_array( $subtreeId ) )
        {
            $subtreeId = array( $subtreeId );
        }
        foreach( $subtreeId as $id )
        {
            if ( !is_numeric( $id ) )
            {
                throw new \InvalidArgumentException( "Only numeric section ids are accepted" );
            }
        }
        $this->operator = Operator::IN;
    }

    /**
     * List of subtree id to match against
     * @var array(integer)
     */
    public $subtreeList;
}
?>
