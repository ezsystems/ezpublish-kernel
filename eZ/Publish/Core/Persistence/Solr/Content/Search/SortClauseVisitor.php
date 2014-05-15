<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Visits the sort clause into a Solr query
 */
abstract class SortClauseVisitor
{
    /**
     * CHeck if visitor is applicable to current sort clause
     *
     * @param SortClause $sortClause
     *
     * @return boolean
     */
    abstract public function canVisit( SortClause $sortClause );

    /**
     * Map field value to a proper Solr representation
     *
     * @param SortClause $sortClause
     *
     * @return string
     */
    abstract public function visit( SortClause $sortClause );

    /**
     * Get solr sort direction for sort clause
     *
     * @param SortClause $sortClause
     *
     * @return string
     */
    protected function getDirection( SortClause $sortClause )
    {
        return ' ' . ( $sortClause->direction === 'descending' ? 'desc' : 'asc' );
    }
}

