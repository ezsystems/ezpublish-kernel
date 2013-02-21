<?php
/**
 * File containing a EzcDatabase sort clause handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\SortClauseHandler;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\SortClauseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use ezcQuerySelect;

/**
 * Content locator gateway implementation using the zeta database component.
 */
class ContentId extends SortClauseHandler
{
    /**
     * Check if this sort clause handler accepts to handle the given sort clause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return boolean
     */
    public function accept( SortClause $sortClause )
    {
        return $sortClause instanceof SortClause\ContentId;
    }

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
    public function applySelect( ezcQuerySelect $query, SortClause $sortClause, $number )
    {
        $query
            ->select(
                $query->alias(
                    $this->dbHandler->quoteColumn(
                        "id",
                        "ezcontentobject"
                    ),
                    $column = $this->getSortColumnName( $number )
                )
            );

        return $column;
    }
}
