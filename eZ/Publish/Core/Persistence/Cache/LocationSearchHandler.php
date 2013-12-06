<?php
/**
 * File containing the LocationSearchHandler implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\Location\Search\Handler as LocationSearchHandlerInterface;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Location\Search\Handler
 */
class LocationSearchHandler extends AbstractHandler implements LocationSearchHandlerInterface
{
    /**
     * Finds locations for given $query.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     */
    public function findLocations( Query $query )
    {
        $this->logger->logCall( __METHOD__, array( 'query' => $query ) );
        return $this->persistenceFactory->getLocationSearchHandler()->findLocations( $query );
    }

    /**
     * Counts all locations given some $criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return int
     */
    public function getLocationCount( Criterion $criterion )
    {
        $this->logger->logCall( __METHOD__, array( 'criterion' => $criterion ) );
        return $this->persistenceFactory->getLocationSearchHandler()->getLocationCount( $criterion );
    }
}
