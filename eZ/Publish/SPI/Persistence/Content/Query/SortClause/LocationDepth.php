<?php
/**
 * File containing the \ezp\Persistence\Content\Query\SortClause\LocationDepth class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Query\SortClause;

use ezp\Content\Query,
    ezp\Persistence\Content\Query\SortClause;

/**
 * Sets sort direction on the location depth string for a content query
 */
class LocationDepth extends SortClause
{
    /**
     * Constructs a new LocationDepth SortClause
     * @param string $sortDirection
     */
    public function __construct( $sortDirection = Query::SORT_ASC )
    {
        parent::__construct( 'location_depth', $sortDirection );
    }
}
