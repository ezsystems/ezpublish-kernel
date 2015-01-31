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
use eZ\Publish\SPI\Search\Content\Location\Handler as LocationSearchHandlerInterface;

/**
 * @see eZ\Publish\SPI\Search\Location\Handler
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
     * Indexes a Location in the index storage
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function indexLocation( Location $location )
    {
        $this->logger->logCall( __METHOD__, array( 'location' => $location->id ) );
        $this->persistenceHandler->locationSearchHandler()->indexLocation( $location );
    }

    /**
     * Deletes a Location from the index storage
     *
     * @param int|string $locationId
     */
    public function deleteLocation( $locationId )
    {
        $this->logger->logCall( __METHOD__, array( 'locationId' => $locationId ) );
        $this->persistenceHandler->locationSearchHandler()->deleteLocation( $locationId );
    }

    /**
     * Deletes a Content from the index storage
     *
     * @param int|string $contentId
     */
    public function deleteContent( $contentId )
    {
        $this->logger->logCall( __METHOD__, array( 'contentId' => $contentId ) );
        $this->persistenceHandler->locationSearchHandler()->deleteContent( $contentId );
    }

    /**
     * Indexes several Location objects at once
     *
     * @todo: This function and setCommit() is needed for Persistence\Solr for test speed but not part
     *       of interface for the reason described in Solr\Content\Search\Gateway\Native::bulkIndexContent
     *       Short: Bulk handling should be properly designed before added to the interface.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location[] $locations
     *
     * @return void
     */
    public function bulkIndexLocations( array $locations )
    {
        $this->persistenceHandler->locationSearchHandler()->bulkIndexLocations( $locations );
    }

    /**
     * Purges all contents from the index
     *
     * @todo: Make this public API?
     *
     * @return void
     */
    public function purgeIndex()
    {
        $this->persistenceHandler->locationSearchHandler()->purgeIndex();
    }

    /**
     * Set if index/delete actions should commit or if several actions is to be expected
     *
     * This should be set to false before group of actions and true before the last one
     * (also, see note on bulkIndexContent())
     * @param bool $commit
     */
    public function setCommit( $commit )
    {
        $this->persistenceHandler->locationSearchHandler()->setCommit( $commit );
    }
}
