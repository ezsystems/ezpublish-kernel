<?php
/**
 * File containing the EzcDatabase sort clause converter class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway,
    eZ\Publish\API\Repository\Values\Content\Query\SortClause,
    eZ\Publish\API\Repository\Values\Content\Query,
    ezcQuerySelect,
    RuntimeException;

/**
 * Converter manager for sort clauses
 */
class SortClauseConverter
{
    /**
     * Sort clause handlers
     *
     * @var array(SortClauseHandler)
     */
    protected $handlers;

    /**
     * Sorting information for temporary sort columns
     *
     * @var array
     */
    protected $sortColumns = array();

    /**
     * Construct from an optional array of sort clause handlers
     *
     * @param array $handlers
     * @return void
     */
    public function __construct( array $handlers = array() )
    {
        $this->handlers = $handlers;
    }

    /**
     * Apply select parts of sort clauses to query
     *
     * @param \ezcQuerySelect $query
     * @param array $sortClauses
     * @return void
     */
    public function applySelect( ezcQuerySelect $query, array $sortClauses )
    {
        $sortColumn = array();
        foreach ( $sortClauses as $nr => $sortClause )
        {
            foreach ( $this->handlers as $handler )
            {
                if ( $handler->accept( $sortClause ) )
                {
                    foreach ( (array)$handler->applySelect( $query, $sortClause, $nr ) as $column )
                    {
                        $this->sortColumns[$column] = $sortClause->direction;
                    }
                    continue 2;
                }
            }

            throw new RuntimeException( 'No handler available for sort clause: ' . get_class( $sortClause ) );
        }
    }

    /**
     * Apply join parts of sort clauses to query
     *
     * @param \ezcQuerySelect $query
     * @param array $sortClauses
     * @return void
     */
    public function applyJoin( ezcQuerySelect $query, array $sortClauses )
    {
        foreach ( $sortClauses as $nr => $sortClause )
        {
            foreach ( $this->handlers as $handler )
            {
                if ( $handler->accept( $sortClause ) )
                {
                    $handler->applyJoin( $query, $sortClause, $nr );
                    continue 2;
                }
            }

            throw new RuntimeException( 'No handler available for sort clause: ' . get_class( $sortClause ) );
        }
    }

    /**
     * Apply order by parts of sort clauses to query
     *
     * @param \ezcQuerySelect $query
     * @param array $sortClauses
     * @return void
     */
    public function applyOrderBy( ezcQuerySelect $query, array $sortClauses )
    {
        foreach ( $this->sortColumns as $column => $direction )
        {
            $query->orderBy(
                $column,
                $direction === Query::SORT_ASC ? ezcQuerySelect::ASC : ezcQuerySelect::DESC
            );
        }
    }
}

