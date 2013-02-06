<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 * @package eZ\Publish\API\Repository\Values\Content\Query
 */

namespace eZ\Publish\API\Repository\Values\Content\Query;

use eZ\Publish\API\Repository\Values\Content\Query;
use InvalidArgumentException;

/**
 * This class is the base for SortClause classes, used to set sorting of content queries
 * @package eZ\Publish\API\Repository\Values\Content\Query
 */
abstract class SortClause
{
    /**
     * Sort direction
     * One of Query::SORT_ASC or Query::SORT_DESC;
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
     * @var SortClause\Target
     */
    public $targetData;

    /**
     * Constructs a new SortClause on $sortTarget in direction $sortDirection
     * @param string $sortTarget
     * @param string $sortDirection one of Query::SORT_ASC or Query::SORT_DESC
     * @param string $targetData Extra target data, used by some clauses (field for instance)
     *
     * @throws InvalidArgumentException if the given sort order isn't one of Query::SORT_ASC or Query::SORT_DESC
     */
    public function __construct( $sortTarget, $sortDirection, $targetData = null )
    {
        if ( $sortDirection !== Query::SORT_ASC && $sortDirection !== Query::SORT_DESC )
        {
            throw new InvalidArgumentException( "Sort direction must be one of Query::SORT_ASC or Query::SORT_DESC" );
        }

        $this->direction = $sortDirection;
        $this->target = $sortTarget;

        if ( $targetData !== null )
        {
            $this->targetData = $targetData;
        }
    }
}
