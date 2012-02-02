<?php
/**
 * File containing the \eZ\Publish\SPI\Persistence\Content\Query\SortClause\LocationPriority class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Query\SortClause;

use ezp\Content\Query,
    eZ\Publish\SPI\Persistence\Content\Query\SortClause;

/**
 * Sets sort direction on the location priority date for a content query
 */
class LocationPriority extends SortClause
{
    /**
     * Constructs a new LocationPriority SortClause
     * @param string $sortDirection
     */
    public function __construct( $sortDirection = Query::SORT_ASC )
    {
        parent::__construct( 'location_priority', $sortDirection );
    }
}
