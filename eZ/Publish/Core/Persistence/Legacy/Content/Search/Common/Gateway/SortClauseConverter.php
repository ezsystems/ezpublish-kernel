<?php
/**
 * File containing the DoctrineDatabase sort clause converter class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use RuntimeException;

/**
 * Converter manager for sort clauses
 */
class SortClauseConverter
{
    /**
     * Sort clause handlers
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\SortClauseHandler[]
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
     */
    public function __construct( array $handlers = array() )
    {
        $this->handlers = $handlers;
    }

    public function addHandler( $handler )
    {
        $this->handlers[] = $handler;
    }

    /**
     * Apply select parts of sort clauses to query
     *
     * @throws \RuntimeException If no handler is available for sort clause
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     */
    public function applySelect( SelectQuery $query, array $sortClauses )
    {
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
     * @throws \RuntimeException If no handler is available for sort clause
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     */
    public function applyJoin( SelectQuery $query, array $sortClauses )
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
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     */
    public function applyOrderBy( SelectQuery $query )
    {
        foreach ( $this->sortColumns as $column => $direction )
        {
            $query->orderBy(
                $column,
                $direction === Query::SORT_ASC ? SelectQuery::ASC : SelectQuery::DESC
            );
        }

        // @todo Review needed
        // The following line was added because without it, loading sub user groups through the Public API
        // fails with the database error "Unknown column sort_column_0". The change does not break any
        // integration tests or legacy persistence tests, but it can break something else, so review is needed
        // Discussion: https://github.com/ezsystems/ezpublish-kernel/commit/8749d0977307858c3e2a7d82f3be90fa21973357#L1R102
        $this->sortColumns = array();
    }
}

