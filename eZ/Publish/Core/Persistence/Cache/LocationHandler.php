<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler
 */
class LocationHandler extends AbstractInMemoryPersistenceHandler implements LocationHandlerInterface
{
    private const CONTENT_IDENTIFIER = 'content';
    private const LOCATION_IDENTIFIER = 'location';
    private const LOCATION_PATH_IDENTIFIER = 'location_path';
    private const LOCATION_REMOTE_ID_IDENTIFIER = 'location_remote_id';
    private const LOCATION_SUBTREE_IDENTIFIER = 'location_subtree';
    private const CONTENT_LOCATIONS_IDENTIFIER = 'content_locations';
    private const CONTENT_LOCATIONS_WITH_PARENT_FOR_DRAFT_SUFFIX_IDENTIFIER = 'content_locations_with_parent_for_draft_suffix';
    private const PARENT_FOR_DRAFT_SUFFIX = 'parent_for_draft_suffix';
    private const ROLE_ASSIGNMENT_GROUP_LIST_IDENTIFIER = 'role_assignment_group_list';

    /** @var callable */
    private $getLocationTags;

    /** @var callable */
    private $getLocationKeys;

    protected function init(): void
    {
        $this->getLocationTags = function (Location $location) {
            $tags = [
                $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$location->contentId]),
                $this->cacheIdentifierGenerator->generateTag(self::LOCATION_IDENTIFIER, [$location->id]),
            ];

            $pathIds = $this->locationPathConverter->convertToPathIds($location->pathString);
            foreach ($pathIds as $pathId) {
                $tags[] = $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$pathId]);
            }

            return $tags;
        };

        $this->getLocationKeys = function (Location $location, $keySuffix = '-1') {
            return [
                $this->cacheIdentifierGenerator->generateKey(self::LOCATION_IDENTIFIER, [$location->id], true) . $keySuffix,
                $this->cacheIdentifierGenerator->generateKey(
                    self::LOCATION_REMOTE_ID_IDENTIFIER,
                    [$this->cacheIdentifierSanitizer->escapeForCacheKey($location->remoteId)],
                    true
                ) . $keySuffix,
            ];
        };
    }

    /**
     * {@inheritdoc}
     */
    public function load($locationId, array $translations = null, bool $useAlwaysAvailable = true)
    {
        $keySuffix = '-' . $this->getCacheTranslationKey($translations, $useAlwaysAvailable);
        $getLocationKeysFn = $this->getLocationKeys;

        return $this->getCacheValue(
            (int) $locationId,
            $this->cacheIdentifierGenerator->generateKey(self::LOCATION_IDENTIFIER, [], true) . '-',
            function ($id) use ($translations, $useAlwaysAvailable) {
                return $this->persistenceHandler->locationHandler()->load($id, $translations, $useAlwaysAvailable);
            },
            $this->getLocationTags,
            static function (Location $location) use ($keySuffix, $getLocationKeysFn) {
                return $getLocationKeysFn($location, $keySuffix);
            },
            $keySuffix,
            ['location' => $locationId, 'translations' => $translations, 'alwaysAvailable' => $useAlwaysAvailable]
        );
    }

    public function loadList(array $locationIds, array $translations = null, bool $useAlwaysAvailable = true): iterable
    {
        $keySuffix = '-' . $this->getCacheTranslationKey($translations, $useAlwaysAvailable);
        $getLocationKeysFn = $this->getLocationKeys;

        return $this->getMultipleCacheValues(
            $locationIds,
            $this->cacheIdentifierGenerator->generateKey(self::LOCATION_IDENTIFIER, [], true) . '-',
            function (array $ids) use ($translations, $useAlwaysAvailable) {
                return $this->persistenceHandler->locationHandler()->loadList($ids, $translations, $useAlwaysAvailable);
            },
            $this->getLocationTags,
            static function (Location $location) use ($keySuffix, $getLocationKeysFn) {
                return $getLocationKeysFn($location, $keySuffix);
            },
            $keySuffix,
            ['location' => $locationIds, 'translations' => $translations, 'alwaysAvailable' => $useAlwaysAvailable]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadSubtreeIds($locationId)
    {
        return $this->getCacheValue(
            (int) $locationId,
            $this->cacheIdentifierGenerator->generateKey(self::LOCATION_SUBTREE_IDENTIFIER, [], true) . '-',
            function (int $locationId): array {
                return $this->persistenceHandler->locationHandler()->loadSubtreeIds($locationId);
            },
            function (array $locationIds) use ($locationId): array {
                $cacheTags = [
                    $this->cacheIdentifierGenerator->generateTag(self::LOCATION_IDENTIFIER, [$locationId]),
                    $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$locationId]),
                ];

                foreach ($locationIds as $id) {
                    $cacheTags[] = $this->cacheIdentifierGenerator->generateTag(self::LOCATION_IDENTIFIER, [$id]);
                    $cacheTags[] = $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$id]);
                }

                return $cacheTags;
            },
            function () use ($locationId): array {
                return [
                    $this->cacheIdentifierGenerator->generateKey(self::LOCATION_SUBTREE_IDENTIFIER, [$locationId], true),
                ];
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadLocationsByContent($contentId, $rootLocationId = null)
    {
        $keySuffix = '';
        $cacheTags = [
            $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$contentId]),
        ];

        if ($rootLocationId) {
            $keySuffix = '-root-' . $rootLocationId;
            $cacheTags[] = $this->cacheIdentifierGenerator->generateTag(self::LOCATION_IDENTIFIER, [$rootLocationId]);
            $cacheTags[] = $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$rootLocationId]);
        }

        return $this->getCacheValue(
            (int) $contentId,
            $this->cacheIdentifierGenerator->generateKey(self::CONTENT_LOCATIONS_IDENTIFIER, [], true) . '-',
            function (int $contentId) use ($rootLocationId): array {
                return $this->persistenceHandler->locationHandler()->loadLocationsByContent($contentId, $rootLocationId);
            },
            function (array $locations) use ($cacheTags): array {
                foreach ($locations as $location) {
                    $cacheTags = $this->getCacheTags($location, $cacheTags);
                }

                return $cacheTags;
            },
            function () use ($contentId, $keySuffix): array {
                return [
                    $this->cacheIdentifierGenerator->generateTag(
                        self::CONTENT_LOCATIONS_IDENTIFIER,
                        [$contentId],
                        true
                    ) . $keySuffix,
                ];
            },
            $keySuffix
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadLocationsByTrashContent(int $contentId, ?int $rootLocationId = null): array
    {
        $this->logger->logCall(__METHOD__, ['content' => $contentId, 'root' => $rootLocationId]);

        return $this->persistenceHandler->locationHandler()->loadLocationsByTrashContent($contentId, $rootLocationId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadParentLocationsForDraftContent($contentId)
    {
        return $this->getCacheValue(
            (int) $contentId,
            $this->cacheIdentifierGenerator->generateKey(self::CONTENT_LOCATIONS_IDENTIFIER, [], true) . '-',
            function (int $contentId): array {
                return $this->persistenceHandler->locationHandler()->loadParentLocationsForDraftContent($contentId);
            },
            function (array $locations) use ($contentId): array {
                $cacheTags = [
                    $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$contentId]),
                ];

                foreach ($locations as $location) {
                    $cacheTags = $this->getCacheTags($location, $cacheTags);
                }

                return $cacheTags;
            },
            function () use ($contentId): array {
                return [
                    $this->cacheIdentifierGenerator->generateKey(
                        self::CONTENT_LOCATIONS_WITH_PARENT_FOR_DRAFT_SUFFIX_IDENTIFIER,
                        [$contentId],
                        true
                    ),
                ];
            },
            $this->cacheIdentifierGenerator->generateKey(self::PARENT_FOR_DRAFT_SUFFIX)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByRemoteId($remoteId, array $translations = null, bool $useAlwaysAvailable = true)
    {
        $keySuffix = '-' . $this->getCacheTranslationKey($translations, $useAlwaysAvailable);
        $getLocationKeysFn = $this->getLocationKeys;

        return $this->getCacheValue(
            $this->cacheIdentifierSanitizer->escapeForCacheKey($remoteId),
            $this->cacheIdentifierGenerator->generateKey(self::LOCATION_REMOTE_ID_IDENTIFIER, [], true) . '-',
            function () use ($remoteId, $translations, $useAlwaysAvailable) {
                return $this->persistenceHandler->locationHandler()->loadByRemoteId($remoteId, $translations, $useAlwaysAvailable);
            },
            $this->getLocationTags,
            static function (Location $location) use ($keySuffix, $getLocationKeysFn) {
                return $getLocationKeysFn($location, $keySuffix);
            },
            $keySuffix,
            ['location' => $remoteId, 'translations' => $translations, 'alwaysAvailable' => $useAlwaysAvailable]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function copySubtree($sourceId, $destinationParentId, $newOwnerId = null)
    {
        $this->logger->logCall(__METHOD__, [
            'source' => $sourceId,
            'destination' => $destinationParentId,
            'newOwner' => $newOwnerId,
        ]);

        return $this->persistenceHandler->locationHandler()->copySubtree($sourceId, $destinationParentId, $newOwnerId);
    }

    /**
     * {@inheritdoc}
     */
    public function move($sourceId, $destinationParentId)
    {
        $this->logger->logCall(__METHOD__, ['source' => $sourceId, 'destination' => $destinationParentId]);
        $return = $this->persistenceHandler->locationHandler()->move($sourceId, $destinationParentId);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$sourceId]),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function markSubtreeModified($locationId, $timestamp = null)
    {
        $this->logger->logCall(__METHOD__, ['location' => $locationId, 'time' => $timestamp]);
        $this->persistenceHandler->locationHandler()->markSubtreeModified($locationId, $timestamp);
    }

    /**
     * {@inheritdoc}
     */
    public function hide($locationId)
    {
        $this->logger->logCall(__METHOD__, ['location' => $locationId]);
        $return = $this->persistenceHandler->locationHandler()->hide($locationId);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$locationId]),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function unHide($locationId)
    {
        $this->logger->logCall(__METHOD__, ['location' => $locationId]);
        $return = $this->persistenceHandler->locationHandler()->unHide($locationId);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$locationId]),
        ]);

        return $return;
    }

    /**
     * Sets a location + all children to invisible.
     *
     * @param int $id Location ID
     */
    public function setInvisible(int $id): void
    {
        $this->logger->logCall(__METHOD__, ['location' => $id]);
        $this->persistenceHandler->locationHandler()->setInvisible($id);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$id]),
        ]);
    }

    /**
     * Sets a location + all children to visible.
     *
     * @param int $id Location ID
     */
    public function setVisible(int $id): void
    {
        $this->logger->logCall(__METHOD__, ['location' => $id]);
        $this->persistenceHandler->locationHandler()->setVisible($id);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$id]),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function swap($locationId1, $locationId2)
    {
        $this->logger->logCall(__METHOD__, ['location1' => $locationId1, 'location2' => $locationId2]);
        $locationHandler = $this->persistenceHandler->locationHandler();

        $return = $locationHandler->swap($locationId1, $locationId2);

        $this->cache->invalidateTags(
            [
                $this->cacheIdentifierGenerator->generateTag(self::LOCATION_IDENTIFIER, [$locationId1]),
                $this->cacheIdentifierGenerator->generateTag(self::LOCATION_IDENTIFIER, [$locationId2]),
            ]
        );

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateStruct $struct, $locationId)
    {
        $this->logger->logCall(__METHOD__, ['location' => $locationId, 'struct' => $struct]);
        $this->persistenceHandler->locationHandler()->update($struct, $locationId);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::LOCATION_IDENTIFIER, [$locationId]),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $locationStruct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $locationStruct]);
        $location = $this->persistenceHandler->locationHandler()->create($locationStruct);

        // need to clear loadLocationsByContent and similar collections involving locations data
        // also need to clear content info on main location changes
        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$locationStruct->contentId]),
            $this->cacheIdentifierGenerator->generateTag(self::ROLE_ASSIGNMENT_GROUP_LIST_IDENTIFIER, [$locationStruct->contentId]),
        ]);

        return $location;
    }

    /**
     * {@inheritdoc}
     */
    public function removeSubtree($locationId)
    {
        $this->logger->logCall(__METHOD__, ['location' => $locationId]);
        $return = $this->persistenceHandler->locationHandler()->removeSubtree($locationId);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$locationId]),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setSectionForSubtree($locationId, $sectionId)
    {
        $this->logger->logCall(__METHOD__, ['location' => $locationId, 'section' => $sectionId]);
        $this->persistenceHandler->locationHandler()->setSectionForSubtree($locationId, $sectionId);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$locationId]),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function changeMainLocation($contentId, $locationId)
    {
        $this->logger->logCall(__METHOD__, ['location' => $locationId, 'content' => $contentId]);
        $this->persistenceHandler->locationHandler()->changeMainLocation($contentId, $locationId);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$contentId]),
        ]);
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
        $this->logger->logCall(__METHOD__, ['offset' => $offset, 'limit' => $limit]);

        return $this->persistenceHandler->locationHandler()->loadAllLocations($offset, $limit);
    }

    /**
     * Return relevant content and location tags so cache can be purged reliably.
     *
     * @param array $tags Optional, can be used to specify additional tags.
     *
     * @return array
     */
    private function getCacheTags(Location $location, $tags = [])
    {
        $tags[] = $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$location->contentId]);
        $tags[] = $this->cacheIdentifierGenerator->generateTag(self::LOCATION_IDENTIFIER, [$location->id]);

        $pathIds = $this->locationPathConverter->convertToPathIds($location->pathString);
        foreach ($pathIds as $pathId) {
            $tags[] = $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$pathId]);
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
