<?php
/**
 * File containing the Location Search Handler interface
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Location\Search;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * The Location Search Handler interface defines search operations on Location elements in the storage engine.
 */
interface Handler
{
    /**
     * Finds all locations given some $criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param int $offset
     * @param int $limit
     */
    public function findLocations( Criterion $criterion, $offset = 0, $limit = 10 );

    /**
     * Counts all locations given some $criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return int
     */
    public function getLocationCount( Criterion $criterion );
}
