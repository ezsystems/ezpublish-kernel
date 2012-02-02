<?php
/**
 * File containing a EzcDatabase sort clause handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Search\Gateway\SortClauseHandler;
use ezp\Persistence\Storage\Legacy\Content\Search\Gateway\SortClauseHandler,
    ezp\Persistence\Storage\Legacy\Content\Search\Gateway,
    ezp\Persistence\Storage\Legacy\EzcDbHandler,
    ezp\Persistence\Content\Query\SortClause,
    ezcQuerySelect;

/**
 * Content locator gateway implementation using the zeta database component.
 */
class LocationPathString extends SortClauseHandler
{
    /**
     * Check if this sort clause handler accepts to handle the given sort clause.
     *
     * @param SortClause $sortClause
     * @return bool
     */
    public function accept( SortClause $sortClause )
    {
        return $sortClause instanceof SortClause\LocationPathString;
    }

    /**
     * Apply selects to the query
     *
     * Returns the name of the (aliased) column, which information should be
     * used for sorting.
     *
     * @param \ezcQuerySelect $query
     * @param SortClause $sortClause
     * @param int $number
     * @return string
     */
    public function applySelect( ezcQuerySelect $query, SortClause $sortClause, $number )
    {
        $query
            ->select(
                $query->alias(
                    $this->dbHandler->quoteColumn(
                        'path_string',
                        $this->getSortTableName( $number )
                    ),
                    $column = $this->getSortColumnName( $number )
                )
            );

        return $column;
    }

    /**
     * Applies joins to the query, required to fetch sort data
     *
     * @param \ezcQuerySelect $query
     * @param SortClause $sortClause
     * @param int $number
     * @return void
     */
    public function applyJoin( ezcQuerySelect $query, SortClause $sortClause, $number )
    {
        $table = $this->getSortTableName( $number );
        $query
            ->leftJoin(
                $query->alias(
                    $this->dbHandler->quoteTable( 'ezcontentobject_tree' ),
                    $this->dbHandler->quoteIdentifier( $table )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id', $table ),
                    $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' )
                )
            );
    }
}

