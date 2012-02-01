<?php
/**
 *
 * @package ezp\PublicAPI\Values\Content\Query
 *
 */
namespace ezp\PublicAPI\Values\Content\Query;

use ezp\PublicAPI\Values\Content\Query,
    InvalidArgumentException;

/**
 * This class is the base for SortClause classes, used to set sorting of content queries
 * @package ezp\PublicAPI\Values\Content\Query
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
