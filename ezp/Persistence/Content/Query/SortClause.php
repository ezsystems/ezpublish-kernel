<?php
/**
 * File containing the ezp\Persistence\Content\Query\SortClause abstract class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Query;

use ezp\Content\Query,
    InvalidArgumentException;

/**
 * This class is the base for SortClause classes, used to set sorting of content queries
 */
abstract class SortClause
{
    /**
     * Sort direction
     * One of \ezp\Content\Query::SORT_ASC or \ezp\Content\Query::SORT_DESC;
     * @var string
     */
    public $direction = Query::SORT_ASC;

    /**
     * Sort target, high level: section_identifier, attribute_value, etc
     * @var string
     */
    public $target;

    /**
     * Extra target data, required by some sort clauses, field for instance
     * @var SortClauseTarget
     */
    public $targetData;

    /**
     * Constructs a new SortClause on $sortTarget in direction $sortDirection
     * @param string $sortTarget
     * @param string $sortDirection one of ezp\Content\Query::SORT_ASC or ezp\Content\Query::SORT_DESC
     * @param string $targetData Extra target data, used by some clauses (field for instance)
     *
     * @throws InvalidArgumentException if the given sort order isn't one of ezp\Content\Query::SORT_ASC or ezp\Content\Query::SORT_DESC
     */
    public function __construct( $sortTarget, $sortDirection, $targetData = null )
    {
        if ( $sortDirection !== Query::SORT_ASC && $sortDirection !== Query::SORT_DESC )
        {
            throw new InvalidArgumentException( "Sort direction must be one of ezp\Content\Query::SORT_ASC or ezp\Content\Query::SORT_DESC" );
        }

        $this->direction = $sortDirection;
        $this->target = $sortTarget;

        if ( $targetData !== null )
        {
            $this->targetData = $targetData;
        }
    }
}
