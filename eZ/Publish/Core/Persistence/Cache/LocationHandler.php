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
use eZ\Publish\Core\Persistence\Factory as PersistenceFactory;
use Tedivm\StashBundle\Service\CacheService;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
 */
class LocationHandler implements LocationHandlerInterface
{
    /**
     * @var \Tedivm\StashBundle\Service\CacheService
     */
    protected $cache;

    /**
     * @var \eZ\Publish\Core\Persistence\Factory
     */
    protected $persistenceFactory;

    /**
     * Setups current handler with everything needed
     *
     * @param \Tedivm\StashBundle\Service\CacheService $cache
     * @param \eZ\Publish\Core\Persistence\Factory $persistenceFactory
     */
    public function __construct( CacheService $cache, PersistenceFactory $persistenceFactory )
    {
        $this->cache = $cache;
        $this->persistenceFactory = $persistenceFactory;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::load
     */
    public function load( $locationId )
    {
        $cache = $this->cache->get( 'location', $locationId );
        $location = $cache->get();
        if ( $cache->isMiss() )
            $cache->set( $location = $this->persistenceFactory->getLocationHandler()->load( $locationId ) );

        return $location;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::loadLocationsByContent
     */
    public function loadLocationsByContent( $contentId, $rootLocationId = null )
    {
        return $this->persistenceFactory->getLocationHandler()->loadLocationsByContent( $contentId, $rootLocationId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::loadByRemoteId
     */
    public function loadByRemoteId( $remoteId )
    {
        return $this->persistenceFactory->getLocationHandler()->loadByRemoteId( $remoteId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::copySubtree
     */
    public function copySubtree( $sourceId, $destinationParentId )
    {
        return $this->persistenceFactory->getLocationHandler()->copySubtree( $sourceId, $destinationParentId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::move
     */
    public function move( $sourceId, $destinationParentId )
    {
        $return = $this->persistenceFactory->getLocationHandler()->move( $sourceId, $destinationParentId );

        $this->cache->clear( 'location' );// path[Identification]String

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::markSubtreeModified
     */
    public function markSubtreeModified( $locationId, $timestamp = null )
    {
        $this->persistenceFactory->getLocationHandler()->markSubtreeModified( $locationId, $timestamp );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::hide
     */
    public function hide( $id )
    {
        $return = $this->persistenceFactory->getLocationHandler()->hide( $id );

        $this->cache->clear( 'location' );// visibility

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::unhide
     */
    public function unHide( $id )
    {
        $return = $this->persistenceFactory->getLocationHandler()->unHide( $id );

        $this->cache->clear( 'location' );// visibility

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::swap
     */
    public function swap( $locationId1, $locationId2 )
    {
        $return = $this->persistenceFactory->getLocationHandler()->swap( $locationId1, $locationId2 );

        $this->cache->clear( 'location', $locationId1 );
        $this->cache->clear( 'location', $locationId2 );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::update
     */
    public function update( UpdateStruct $location, $locationId )
    {
        $this->cache
            ->get( 'location', $locationId )
            ->set( $location = $this->persistenceFactory->getLocationHandler()->update( $location, $locationId ) );

        return $location;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::create
     */
    public function create( CreateStruct $locationStruct )
    {
        $location = $this->persistenceFactory->getLocationHandler()->create( $locationStruct );

        $this->cache->get( 'location', $location->id )->set( $location );

        return $location;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::removeSubtree
     */
    public function removeSubtree( $locationId )
    {
        $return = $this->persistenceFactory->getLocationHandler()->removeSubtree( $locationId );

        $this->cache->clear( 'location' );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::setSectionForSubtree
     */
    public function setSectionForSubtree( $locationId, $sectionId )
    {
        $this->persistenceFactory->getLocationHandler()->setSectionForSubtree( $locationId, $sectionId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::changeMainLocation
     */
    public function changeMainLocation( $contentId, $locationId )
    {
        $this->persistenceFactory->getLocationHandler()->changeMainLocation( $contentId, $locationId );
        $this->cache->clear( 'location' );// ->mainLocationId
    }
}
