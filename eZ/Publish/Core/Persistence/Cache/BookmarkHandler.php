<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use eZ\Publish\SPI\Persistence\Bookmark\CreateStruct;
use eZ\Publish\SPI\Persistence\Bookmark\Handler as BookmarkHandlerInterface;

/**
 * @see \eZ\Publish\SPI\Persistence\Bookmark\Handler
 */
class BookmarkHandler extends AbstractHandler implements BookmarkHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $createStruct): Bookmark
    {
        $this->logger->logCall(__METHOD__, [
            'createStruct' => $createStruct,
        ]);

        return $this->persistenceHandler->bookmarkHandler()->create($createStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $bookmarkId): void
    {
        $this->logger->logCall(__METHOD__, [
            'id' => $bookmarkId,
        ]);

        $this->persistenceHandler->bookmarkHandler()->delete($bookmarkId);

        $this->cache->invalidateTags(['bookmark-' . $bookmarkId]);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByUserIdAndLocationId(int $userId, array $locationIds): array
    {
        return $this->getMultipleCacheItems(
            $locationIds,
            'ez-bookmark-' . $userId . '-',
            function (array $missingIds) use ($userId) {
                $this->logger->logCall(__CLASS__ . '::loadByUserIdAndLocationId', [
                    'userId' => $userId,
                    'locationIds' => $missingIds,
                ]);

                return $this->persistenceHandler->bookmarkHandler()->loadByUserIdAndLocationId($userId, $missingIds);
            },
            function (Bookmark $bookmark) {
                $tags = [
                    'bookmark-' . $bookmark->id,
                    'location-' . $bookmark->locationId,
                    'user-' . $bookmark->userId,
                ];

                $location = $this->persistenceHandler->locationHandler()->load($bookmark->locationId);
                foreach (explode('/', trim($location->pathString, '/')) as $locationId) {
                    $tags[] = 'location-path-' . $locationId;
                }

                return $tags;
            },
            function () use ($userId, $locationIds) {
                $this->logger->logCacheHit(__CLASS__ . '::loadByUserIdAndLocationId', [
                    'userId' => $userId,
                    'locationIds' => $locationIds,
                ]);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserBookmarks(int $userId, int $offset = 0, int $limit = -1): array
    {
        $this->logger->logCall(__METHOD__, [
            'userId' => $userId,
            'offset' => $offset,
            'limit' => $limit,
        ]);

        return $this->persistenceHandler->bookmarkHandler()->loadUserBookmarks($userId, $offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function countUserBookmarks(int $userId): int
    {
        $this->logger->logCall(__METHOD__, [
            'userId' => $userId,
        ]);

        return $this->persistenceHandler->bookmarkHandler()->countUserBookmarks($userId);
    }

    /**
     * {@inheritdoc}
     */
    public function locationSwapped(int $location1Id, int $location2Id): void
    {
        $this->logger->logCall(__METHOD__, [
            'location1Id' => $location1Id,
            'location2Id' => $location2Id,
        ]);

        // Cache clearing is already done by locationHandler
        $this->persistenceHandler->bookmarkHandler()->locationSwapped($location1Id, $location2Id);
    }
}
