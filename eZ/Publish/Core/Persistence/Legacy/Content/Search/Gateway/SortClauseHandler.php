<?php
/**
 * File containing the EzcDatabase sort clause handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway;

use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use ezcQuerySelect;

/**
 * Handler for a single sort clause
 */
abstract class SortClauseHandler
{
    /**
     * Database handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Creates a new criterion handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     */
    public function __construct( EzcDbHandler $dbHandler )
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * Check if this sort clause handler accepts to handle the given sort clause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return boolean
     */
    abstract public function accept( SortClause $sortClause );

    /**
     * Apply selects to the query
     *
     * Returns the name of the (aliased) column, which information should be
     * used for sorting.
     *
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     *
     * @return string
     */
    abstract public function applySelect( ezcQuerySelect $query, SortClause $sortClause, $number );

    /**
     * Applies joins to the query
     *
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     *
     * @return void
     */
    public function applyJoin( ezcQuerySelect $query, SortClause $sortClause, $number )
    {
    }

    /**
     * Returns the quoted sort column name
     *
     * @param int $number
     *
     * @return string
     */
    protected function getSortColumnName( $number )
    {
        return $this->dbHandler->quoteIdentifier( 'sort_column_' . $number );
    }

    /**
     * Returns the sort table name
     *
     * @param int $number
     *
     * @return string
     */
    protected function getSortTableName( $number )
    {
        return 'sort_table_' . $number;
    }
}

