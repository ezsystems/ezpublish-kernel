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
    private const BOOKMARK_TAG = 'bookmark';
    private const PREFIXED_BOOKMARK_TAG = 'prefixed_bookmark';
    private const LOCATION_TAG = 'location';
    private const USER_TAG = 'user';
    private const LOCATION_PATH_TAG = 'location_path';

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

        $this->cache->invalidateTags([
            $this->tagGenerator->generate(self::BOOKMARK_TAG, [$bookmarkId]),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByUserIdAndLocationId(int $userId, array $locationIds): array
    {
        $tagGenerator = $this->tagGenerator;

        return $this->getMultipleCacheItems(
            $locationIds,
            $tagGenerator->generate(self::PREFIXED_BOOKMARK_TAG, [$userId], true),
            function (array $missingIds) use ($userId) {
                $this->logger->logCall(__CLASS__ . '::loadByUserIdAndLocationId', [
                    'userId' => $userId,
                    'locationIds' => $missingIds,
                ]);

                return $this->persistenceHandler->bookmarkHandler()->loadByUserIdAndLocationId($userId, $missingIds);
            },
            function (Bookmark $bookmark) use ($tagGenerator) {
                $tags = [
                    $tagGenerator->generate(self::BOOKMARK_TAG, [$bookmark->id]),
                    $tagGenerator->generate(self::LOCATION_TAG, [$bookmark->locationId]),
                    $tagGenerator->generate(self::USER_TAG, [$bookmark->userId]),
                ];

                $location = $this->persistenceHandler->locationHandler()->load($bookmark->locationId);
                foreach (explode('/', trim($location->pathString, '/')) as $locationId) {
                    $tags[] = $tagGenerator->generate(self::LOCATION_PATH_TAG, [$locationId]);
                }

                return $tags;
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
