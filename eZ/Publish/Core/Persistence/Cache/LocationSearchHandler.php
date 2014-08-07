<?php
/**
 * File containing the LocationSearchHandler implementation
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\Search\Handler as LocationSearchHandlerInterface;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Location\Search\Handler
 */
class LocationSearchHandler extends AbstractHandler implements LocationSearchHandlerInterface
{
    /**
     * Finds locations for given $query.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     */
    public function findLocations( LocationQuery $query )
    {
        $this->logger->logCall( __METHOD__, array( 'query' => $query ) );
        return $this->persistenceHandler->locationSearchHandler()->findLocations( $query );
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function indexLocation( Location $location )
    {
        $this->logger->logCall( __METHOD__, array( 'location' => $location->id ) );
        $this->persistenceHandler->locationSearchHandler()->indexLocation( $location );
    }

    /**
     * @param int|string $locationId
     */
    public function deleteLocation( $locationId )
    {
        $this->logger->logCall( __METHOD__, array( 'locationId' => $locationId ) );
        $this->persistenceHandler->locationSearchHandler()->deleteLocation( $locationId );
    }

    /**
     * @param int|string $contentId
     */
    public function deleteContent( $contentId )
    {
        $this->logger->logCall( __METHOD__, array( 'contentId' => $contentId ) );
        $this->persistenceHandler->locationSearchHandler()->deleteContent( $contentId );
    }
}
