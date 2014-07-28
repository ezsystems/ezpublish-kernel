<?php
/**
 * File containing the base SortClauseVisitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Visits the sort clause into a Elasticsearch query
 */
abstract class SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current sort clause
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return boolean
     */
    abstract public function canVisit( SortClause $sortClause );

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return string
     */
    abstract public function visit( SortClause $sortClause );

    /**
     * Get Elasticsearch sort direction for sort clause
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return string
     */
    protected function getDirection( SortClause $sortClause )
    {
        return ( $sortClause->direction === "descending" ? "desc" : "asc" );
    }
}

