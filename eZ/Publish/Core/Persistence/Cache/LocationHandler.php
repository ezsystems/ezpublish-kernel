<?php

/**
 * File containing the LocationHandler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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
    public function load($locationId)
    {
        $cache = $this->cache->getItem('location', $locationId);
        $location = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->startLogCall(__METHOD__, array('location' => $locationId));
            $cache->set($location = $this->persistenceHandler->locationHandler()->load($locationId));
            $this->logger->stopLogCall(__METHOD__);
        }

        return $location;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::loadSubtreeIds
     */
    public function loadSubtreeIds($locationId)
    {
        $cache = $this->cache->getItem('location', 'subtree', $locationId);
        $locationIds = $cache->get();

        if ($cache->isMiss()) {
            $this->logger->startLogCall(__METHOD__, array('location' => $locationId));
            $cache->set(
                $locationIds = $this->persistenceHandler->locationHandler()->loadSubtreeIds($locationId)
            );
            $this->logger->stopLogCall(__METHOD__);
        }

        return $locationIds;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::loadLocationsByContent
     */
    public function loadLocationsByContent($contentId, $rootLocationId = null)
    {
        if ($rootLocationId) {
            $cache = $this->cache->getItem('content', 'locations', $contentId, 'root', $rootLocationId);
        } else {
            $cache = $this->cache->getItem('content', 'locations', $contentId);
        }
        $locationIds = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->startLogCall(__METHOD__, array('content' => $contentId, 'root' => $rootLocationId));
            $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($contentId, $rootLocationId);

            // Add checkpoint
            $this->logger->lapLogCall(__METHOD__);

            $locationIds = array();
            foreach ($locations as $location) {
                $locationIds[] = $location->id;
            }

            $cache->set($locationIds);

            $this->logger->stopLogCall(__METHOD__);
        } else {
            $locations = array();
            foreach ($locationIds as $locationId) {
                $locations[] = $this->load($locationId);
            }
        }

        return $locations;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::loadParentLocationsForDraftContent
     */
    public function loadParentLocationsForDraftContent($contentId)
    {
        $cache = $this->cache->getItem('content', 'locations', $contentId, 'parentLocationsForDraftContent');
        $locationIds = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->startLogCall(__METHOD__, array('content' => $contentId));
            $locations = $this->persistenceHandler->locationHandler()->loadParentLocationsForDraftContent($contentId);

            // Add checkpoint
            $this->logger->lapLogCall(__METHOD__);

            $locationIds = array();
            foreach ($locations as $location) {
                $locationIds[] = $location->id;
            }

            $cache->set($locationIds);

            $this->logger->stopLogCall(__METHOD__);
        } else {
            $locations = array();
            foreach ($locationIds as $locationId) {
                $locations[] = $this->load($locationId);
            }
        }

        return $locations;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::loadByRemoteId
     */
    public function loadByRemoteId($remoteId)
    {
        $this->logger->startLogCall(__METHOD__, array('location' => $remoteId));

        $return = $this->persistenceHandler->locationHandler()->loadByRemoteId($remoteId);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::copySubtree
     */
    public function copySubtree($sourceId, $destinationParentId)
    {
        $this->logger->startLogCall(__METHOD__, array('source' => $sourceId, 'destination' => $destinationParentId));

        $return = $this->persistenceHandler->locationHandler()->copySubtree($sourceId, $destinationParentId);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::move
     */
    public function move($sourceId, $destinationParentId)
    {
        $this->logger->startLogCall(__METHOD__, array('source' => $sourceId, 'destination' => $destinationParentId));
        $return = $this->persistenceHandler->locationHandler()->move($sourceId, $destinationParentId);

        // Add checkpoint
        $this->logger->lapLogCall(__METHOD__);

        $this->cache->clear('location');//TIMBER! (path[Identification]String)
        $this->cache->clear('user', 'role', 'assignments', 'byGroup');

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::markSubtreeModified
     */
    public function markSubtreeModified($locationId, $timestamp = null)
    {
        $this->logger->startLogCall(__METHOD__, array('location' => $locationId, 'time' => $timestamp));
        $this->persistenceHandler->locationHandler()->markSubtreeModified($locationId, $timestamp);
        $this->logger->stopLogCall(__METHOD__);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::hide
     */
    public function hide($locationId)
    {
        $this->logger->startLogCall(__METHOD__, array('location' => $locationId));
        $return = $this->persistenceHandler->locationHandler()->hide($locationId);

        // Add checkpoint
        $this->logger->lapLogCall(__METHOD__);

        $this->cache->clear('location');//TIMBER! (visibility)

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::unhide
     */
    public function unHide($locationId)
    {
        $this->logger->startLogCall(__METHOD__, array('location' => $locationId));
        $return = $this->persistenceHandler->locationHandler()->unHide($locationId);

        // Add checkpoint
        $this->logger->lapLogCall(__METHOD__);

        $this->cache->clear('location');//TIMBER! (visibility)

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::swap
     */
    public function swap($locationId1, $locationId2)
    {
        $this->logger->startLogCall(__METHOD__, array('location1' => $locationId1, 'location2' => $locationId2));
        $return = $this->persistenceHandler->locationHandler()->swap($locationId1, $locationId2);

        // Add checkpoint
        $this->logger->lapLogCall(__METHOD__);

        $this->cache->clear('location', $locationId1);
        $this->cache->clear('location', $locationId2);
        $this->cache->clear('location', 'subtree');
        $this->cache->clear('content', 'locations');
        $this->cache->clear('user', 'role', 'assignments', 'byGroup');

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::update
     */
    public function update(UpdateStruct $struct, $locationId)
    {
        $this->logger->startLogCall(__METHOD__, array('location' => $locationId, 'struct' => $struct));
        $this->persistenceHandler->locationHandler()->update($struct, $locationId);

        // Add checkpoint
        $this->logger->lapLogCall(__METHOD__);

        $this->cache->clear('location', $locationId);
        $this->cache->clear('location', 'subtree');

        $this->logger->stopLogCall(__METHOD__);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::create
     */
    public function create(CreateStruct $locationStruct)
    {
        $this->logger->startLogCall(__METHOD__, array('struct' => $locationStruct));
        $location = $this->persistenceHandler->locationHandler()->create($locationStruct);

        // Add checkpoint
        $this->logger->lapLogCall(__METHOD__);

        $this->cache->getItem('location', $location->id)->set($location);
        $this->cache->clear('location', 'subtree');
        $this->cache->clear('content', 'locations', $location->contentId);
        $this->cache->clear('content', $location->contentId);
        $this->cache->clear('content', 'info', $location->contentId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', $location->contentId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', 'inherited', $location->contentId);

        $this->logger->stopLogCall(__METHOD__);

        return $location;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::removeSubtree
     */
    public function removeSubtree($locationId)
    {
        $this->logger->startLogCall(__METHOD__, array('location' => $locationId));
        $return = $this->persistenceHandler->locationHandler()->removeSubtree($locationId);

        // Add checkpoint
        $this->logger->lapLogCall(__METHOD__);

        $this->cache->clear('location');//TIMBER!
        $this->cache->clear('content');//TIMBER!
        $this->cache->clear('user', 'role', 'assignments', 'byGroup');

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::setSectionForSubtree
     */
    public function setSectionForSubtree($locationId, $sectionId)
    {
        $this->logger->startLogCall(__METHOD__, array('location' => $locationId, 'section' => $sectionId));
        $this->persistenceHandler->locationHandler()->setSectionForSubtree($locationId, $sectionId);

        // Add checkpoint
        $this->logger->lapLogCall(__METHOD__);

        $this->cache->clear('content');//TIMBER!

        $this->logger->stopLogCall(__METHOD__);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::changeMainLocation
     */
    public function changeMainLocation($contentId, $locationId)
    {
        $this->logger->startLogCall(__METHOD__, array('location' => $locationId, 'content' => $contentId));
        $this->persistenceHandler->locationHandler()->changeMainLocation($contentId, $locationId);

        // Add checkpoint
        $this->logger->lapLogCall(__METHOD__);

        $this->cache->clear('content', $contentId);
        $this->cache->clear('content', 'info', $contentId);
        $this->cache->clear('content', 'info', 'remoteId');

        $this->logger->stopLogCall(__METHOD__);
    }
}
