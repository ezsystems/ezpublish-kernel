<?php
/**
 * File containing the LocationHandler implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Location;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
 */
class LocationHandler extends AbstractHandler implements LocationHandlerInterface
{
    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::load
     */
    public function load( $locationId )
    {
        $cache = $this->cache->getItem( 'location', $locationId );
        $location = $cache->get();
        if ( $cache->isMiss() )
        {
            $this->logger->logCall( __METHOD__, array( 'location' => $locationId ) );
            $cache->set( $location = $this->persistenceFactory->getLocationHandler()->load( $locationId ) );
        }

        return $location;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::loadLocationsByContent
     */
    public function loadLocationsByContent( $contentId, $rootLocationId = null )
    {
        $rootKey = $rootLocationId ? '/root/' . $rootLocationId : '';
        $cache = $this->cache->getItem( 'content', 'locations', $contentId . $rootKey );
        $locationIds = $cache->get();
        if ( $cache->isMiss() )
        {
            $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'root' => $rootLocationId ) );
            $locations = $this->persistenceFactory->getLocationHandler()->loadLocationsByContent( $contentId, $rootLocationId );

            $locationIds = array();
            foreach ( $locations as $location )
                $locationIds[] = $location->id;

            $cache->set( $locationIds );
        }
        else
        {
            $locations = array();
            foreach ( $locationIds as $locationId )
                $locations[] = $this->load( $locationId );
        }

        return $locations;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::loadByRemoteId
     */
    public function loadByRemoteId( $remoteId )
    {
        $this->logger->logCall( __METHOD__, array( 'location' => $remoteId ) );
        return $this->persistenceFactory->getLocationHandler()->loadByRemoteId( $remoteId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::copySubtree
     */
    public function copySubtree( $sourceId, $destinationParentId )
    {
        $this->logger->logCall( __METHOD__, array( 'source' => $sourceId, 'destination' => $destinationParentId ) );
        return $this->persistenceFactory->getLocationHandler()->copySubtree( $sourceId, $destinationParentId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::move
     */
    public function move( $sourceId, $destinationParentId )
    {
        $this->logger->logCall( __METHOD__, array( 'source' => $sourceId, 'destination' => $destinationParentId ) );
        $return = $this->persistenceFactory->getLocationHandler()->move( $sourceId, $destinationParentId );

        $this->cache->clear( 'location' );//TIMBER! (path[Identification]String)

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::markSubtreeModified
     */
    public function markSubtreeModified( $locationId, $timestamp = null )
    {
        $this->logger->logCall( __METHOD__, array( 'location' => $locationId, 'time' => $timestamp ) );
        $this->persistenceFactory->getLocationHandler()->markSubtreeModified( $locationId, $timestamp );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::hide
     */
    public function hide( $locationId )
    {
        $this->logger->logCall( __METHOD__, array( 'location' => $locationId ) );
        $return = $this->persistenceFactory->getLocationHandler()->hide( $locationId );

        $this->cache->clear( 'location' );//TIMBER! (visibility)

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::unhide
     */
    public function unHide( $locationId )
    {
        $this->logger->logCall( __METHOD__, array( 'location' => $locationId ) );
        $return = $this->persistenceFactory->getLocationHandler()->unHide( $locationId );

        $this->cache->clear( 'location' );//TIMBER! (visibility)

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::swap
     */
    public function swap( $locationId1, $locationId2 )
    {
        $this->logger->logCall( __METHOD__, array( 'location1' => $locationId1, 'location2' => $locationId2 ) );
        $return = $this->persistenceFactory->getLocationHandler()->swap( $locationId1, $locationId2 );

        $this->cache->clear( 'location', $locationId1 );
        $this->cache->clear( 'location', $locationId2 );
        $this->cache->clear( 'content', 'locations' );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::update
     */
    public function update( UpdateStruct $struct, $locationId )
    {
        $this->logger->logCall( __METHOD__, array( 'location' => $locationId, 'struct' => $struct ) );
        $this->cache
            ->getItem( 'location', $locationId )
            ->set( $location = $this->persistenceFactory->getLocationHandler()->update( $struct, $locationId ) );

        return $location;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::create
     */
    public function create( CreateStruct $locationStruct )
    {
        $this->logger->logCall( __METHOD__, array( 'struct' => $locationStruct ) );
        $location = $this->persistenceFactory->getLocationHandler()->create( $locationStruct );

        $this->cache->getItem( 'location', $location->id )->set( $location );
        $this->cache->clear( 'content', 'locations', $location->contentId );

        return $location;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::removeSubtree
     */
    public function removeSubtree( $locationId )
    {
        $this->logger->logCall( __METHOD__, array( 'location' => $locationId ) );
        $return = $this->persistenceFactory->getLocationHandler()->removeSubtree( $locationId );

        $this->cache->clear( 'location' );//TIMBER!
        $this->cache->clear( 'content' );//TIMBER!

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::setSectionForSubtree
     */
    public function setSectionForSubtree( $locationId, $sectionId )
    {
        $this->logger->logCall( __METHOD__, array( 'location' => $locationId, 'section' => $sectionId ) );
        $this->persistenceFactory->getLocationHandler()->setSectionForSubtree( $locationId, $sectionId );
        $this->cache->clear( 'content', 'info' );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::changeMainLocation
     */
    public function changeMainLocation( $contentId, $locationId )
    {
        $this->logger->logCall( __METHOD__, array( 'location' => $locationId, 'content' => $contentId ) );
        $this->persistenceFactory->getLocationHandler()->changeMainLocation( $contentId, $locationId );
        $this->cache->clear( 'content', 'info', $contentId );
    }
}
