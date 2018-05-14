<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
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
    public function loadById(int $bookmarkId): Bookmark
    {
        $cacheItem = $this->cache->getItem('ez-bookmark-' . $bookmarkId);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, [
            'id' => $bookmarkId,
        ]);

        $bookmark = $this->persistenceHandler->bookmarkHandler()->loadById($bookmarkId);
        $cacheItem->set($bookmark);
        $cacheItem->tag([
            'bookmark-' . $bookmarkId,
            'location-' . $bookmark->locationId,
        ]);
        $this->cache->save($cacheItem);

        return $bookmark;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByUserIdAndLocationId(int $userId, int $locationId): Bookmark
    {
        $cacheItem = $this->cache->getItem('ez-bookmark-' . $userId . '-' . $locationId);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, [
            'userId' => $userId,
            'locationId' => $locationId,
        ]);

        $bookmark = $this->persistenceHandler->bookmarkHandler()->loadByUserIdAndLocationId($userId, $locationId);

        $cacheItem->set($bookmark);
        $cacheItem->tag([
            'bookmark-' . $bookmark->id,
            'location-' . $bookmark->locationId,
        ]);
        $this->cache->save($cacheItem);

        return $bookmark;
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
}
