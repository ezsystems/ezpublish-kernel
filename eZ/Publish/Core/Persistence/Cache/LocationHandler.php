<?php

/**
 * File containing the LocationHandler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Location;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler
 */
class LocationHandler extends AbstractHandler implements LocationHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function load($locationId, array $translations = null, bool $useAlwaysAvailable = true)
    {
        $translationsKey = $this->getCacheTranslationKey($translations, $useAlwaysAvailable);
        $cacheItem = $this->cache->getItem("ez-location-${locationId}-${translationsKey}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(
            __METHOD__,
            ['location' => $locationId, 'translations' => $translations, 'always-available' => $useAlwaysAvailable]
        );
        $location = $this->persistenceHandler->locationHandler()->load($locationId, $translations, $useAlwaysAvailable);

        $cacheItem->set($location);
        $cacheItem->tag($this->getCacheTags($location));
        $this->cache->save($cacheItem);

        return $location;
    }

    public function loadList(array $locationIds, array $translations = null, bool $useAlwaysAvailable = true): iterable
    {
        return $this->getMultipleCacheItems(
            $locationIds,
            'ez-location-',
            function (array $cacheMissIds) use ($translations, $useAlwaysAvailable) {
                $this->logger->logCall(
                    __CLASS__ . '::loadList',
                    ['location' => $cacheMissIds, 'translations' => $translations, 'always-available' => $useAlwaysAvailable]
                );

                return $this->persistenceHandler->locationHandler()->loadList($cacheMissIds, $translations, $useAlwaysAvailable);
            },
            function (Location $location) {
                return $this->getCacheTags($location);
            },
            array_fill_keys($locationIds, '-' . $this->getCacheTranslationKey($translations, $useAlwaysAvailable))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadSubtreeIds($locationId)
    {
        $cacheItem = $this->cache->getItem("ez-location-subtree-${locationId}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('location' => $locationId));
        $locationIds = $this->persistenceHandler->locationHandler()->loadSubtreeIds($locationId);

        $cacheItem->set($locationIds);
        $cacheTags = ['location-' . $locationId, 'location-path-' . $locationId];
        foreach ($locationIds as $id) {
            $cacheTags[] = 'location-' . $id;
            $cacheTags[] = 'location-path-' . $id;
        }
        $cacheItem->tag($cacheTags);
        $this->cache->save($cacheItem);

        return $locationIds;
    }

    /**
     * {@inheritdoc}
     */
    public function loadLocationsByContent($contentId, $rootLocationId = null)
    {
        if ($rootLocationId) {
            $cacheItem = $this->cache->getItem("ez-content-locations-${contentId}-root-${rootLocationId}");
            $cacheTags = ['content-' . $contentId, 'location-' . $rootLocationId, 'location-path-' . $rootLocationId];
        } else {
            $cacheItem = $this->cache->getItem("ez-content-locations-${contentId}");
            $cacheTags = ['content-' . $contentId];
        }

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'root' => $rootLocationId));
        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($contentId, $rootLocationId);

        $cacheItem->set($locations);
        foreach ($locations as $location) {
            $cacheTags = $this->getCacheTags($location, $cacheTags);
        }
        $cacheItem->tag($cacheTags);
        $this->cache->save($cacheItem);

        return $locations;
    }

    /**
     * {@inheritdoc}
     */
    public function loadParentLocationsForDraftContent($contentId)
    {
        $cacheItem = $this->cache->getItem("ez-content-locations-${contentId}-parentForDraft");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('content' => $contentId));
        $locations = $this->persistenceHandler->locationHandler()->loadParentLocationsForDraftContent($contentId);

        $cacheItem->set($locations);
        $cacheTags = ['content-' . $contentId];
        foreach ($locations as $location) {
            $cacheTags = $this->getCacheTags($location, $cacheTags);
        }
        $cacheItem->tag($cacheTags);
        $this->cache->save($cacheItem);

        return $locations;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByRemoteId($remoteId, array $translations = null, bool $useAlwaysAvailable = true)
    {
        $translationsKey = $this->getCacheTranslationKey($translations, $useAlwaysAvailable);
        $cacheItem = $this->cache->getItem("ez-location-remoteid-${remoteId}-${translationsKey}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(
            __METHOD__,
            ['location' => $remoteId, 'translations' => $translations, 'always-available' => $useAlwaysAvailable]
        );
        $location = $this->persistenceHandler->locationHandler()->loadByRemoteId($remoteId, $translations, $useAlwaysAvailable);

        $cacheItem->set($location);
        $cacheItem->tag($this->getCacheTags($location));
        $this->cache->save($cacheItem);

        return $location;
    }

    /**
     * {@inheritdoc}
     */
    public function copySubtree($sourceId, $destinationParentId, $newOwnerId = null)
    {
        $this->logger->logCall(__METHOD__, array(
            'source' => $sourceId,
            'destination' => $destinationParentId,
            'newOwner' => $newOwnerId,
        ));

        return $this->persistenceHandler->locationHandler()->copySubtree($sourceId, $destinationParentId, $newOwnerId);
    }

    /**
     * {@inheritdoc}
     */
    public function move($sourceId, $destinationParentId)
    {
        $this->logger->logCall(__METHOD__, array('source' => $sourceId, 'destination' => $destinationParentId));
        $return = $this->persistenceHandler->locationHandler()->move($sourceId, $destinationParentId);

        $this->cache->invalidateTags(['location-path-' . $sourceId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function markSubtreeModified($locationId, $timestamp = null)
    {
        $this->logger->logCall(__METHOD__, array('location' => $locationId, 'time' => $timestamp));
        $this->persistenceHandler->locationHandler()->markSubtreeModified($locationId, $timestamp);
    }

    /**
     * {@inheritdoc}
     */
    public function hide($locationId)
    {
        $this->logger->logCall(__METHOD__, array('location' => $locationId));
        $return = $this->persistenceHandler->locationHandler()->hide($locationId);

        $this->cache->invalidateTags(['location-path-data-' . $locationId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function unHide($locationId)
    {
        $this->logger->logCall(__METHOD__, array('location' => $locationId));
        $return = $this->persistenceHandler->locationHandler()->unHide($locationId);

        $this->cache->invalidateTags(['location-path-data-' . $locationId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function swap($locationId1, $locationId2)
    {
        $this->logger->logCall(__METHOD__, array('location1' => $locationId1, 'location2' => $locationId2));
        $locationHandler = $this->persistenceHandler->locationHandler();

        $return = $locationHandler->swap($locationId1, $locationId2);

        $this->cache->invalidateTags(
            [
                'location-' . $locationId1,
                'location-' . $locationId2,
            ]
        );

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateStruct $struct, $locationId)
    {
        $this->logger->logCall(__METHOD__, array('location' => $locationId, 'struct' => $struct));
        $this->persistenceHandler->locationHandler()->update($struct, $locationId);

        $this->cache->invalidateTags(['location-data-' . $locationId]);
    }

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $locationStruct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $locationStruct));
        $location = $this->persistenceHandler->locationHandler()->create($locationStruct);

        // need to clear loadLocationsByContent and similar collections involving locations data
        // also need to clear content info on main location changes
        $this->cache->invalidateTags(['content-' . $locationStruct->contentId, 'role-assignment-group-list-' . $locationStruct->contentId]);

        return $location;
    }

    /**
     * {@inheritdoc}
     */
    public function removeSubtree($locationId)
    {
        $this->logger->logCall(__METHOD__, array('location' => $locationId));
        $return = $this->persistenceHandler->locationHandler()->removeSubtree($locationId);

        $this->cache->invalidateTags(['location-path-' . $locationId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setSectionForSubtree($locationId, $sectionId)
    {
        $this->logger->logCall(__METHOD__, array('location' => $locationId, 'section' => $sectionId));
        $this->persistenceHandler->locationHandler()->setSectionForSubtree($locationId, $sectionId);

        $this->cache->invalidateTags(['location-path-' . $locationId]);
    }

    /**
     * {@inheritdoc}
     */
    public function changeMainLocation($contentId, $locationId)
    {
        $this->logger->logCall(__METHOD__, array('location' => $locationId, 'content' => $contentId));
        $this->persistenceHandler->locationHandler()->changeMainLocation($contentId, $locationId);

        $this->cache->invalidateTags(['content-' . $contentId]);
    }

    /**
     * Get the total number of all existing Locations. Can be combined with loadAllLocations.
     *
     * @return int
     */
    public function countAllLocations()
    {
        $this->logger->logCall(__METHOD__);

        return $this->persistenceHandler->locationHandler()->countAllLocations();
    }

    /**
     * Bulk-load all existing Locations, constrained by $limit and $offset to paginate results.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location[]
     */
    public function loadAllLocations($offset, $limit)
    {
        $this->logger->logCall(__METHOD__, array('offset' => $offset, 'limit' => $limit));

        return $this->persistenceHandler->locationHandler()->loadAllLocations($offset, $limit);
    }

    /**
     * Return relevant content and location tags so cache can be purged reliably.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     * @param array $tags Optional, can be used to specify additional tags.
     *
     * @return array
     */
    private function getCacheTags(Location $location, $tags = [])
    {
        $tags[] = 'content-' . $location->contentId;
        $tags[] = 'location-' . $location->id;
        $tags[] = 'location-data-' . $location->id;
        foreach (explode('/', trim($location->pathString, '/')) as $pathId) {
            $tags[] = 'location-path-' . $pathId;
            $tags[] = 'location-path-data-' . $pathId;
        }

        return $tags;
    }

    private function getCacheTranslationKey(array $translations = null, bool $useAlwaysAvailable = true): string
    {
        if (empty($translations)) {
            return (int)$useAlwaysAvailable;
        }

        // Sort array as we don't care about order in location handler usage & want to optimize for cache hits.
        sort($translations);

        return implode('|', $translations) . '|' . (int)$useAlwaysAvailable;
    }
}
