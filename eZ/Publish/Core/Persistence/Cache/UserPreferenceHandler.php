<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\UserPreference\UserPreferenceSetStruct;
use eZ\Publish\SPI\Persistence\UserPreference\Handler;
use eZ\Publish\SPI\Persistence\UserPreference\UserPreference;

/**
 * SPI cache for UserPreference Handler.
 *
 * @see \eZ\Publish\SPI\Persistence\UserPreference\Handler
 */
class UserPreferenceHandler extends AbstractHandler implements Handler
{
    /**
     * {@inheritdoc}
     */
    public function setUserPreference(UserPreferenceSetStruct $setStruct): UserPreference
    {
        $this->logger->logCall(__METHOD__, [
            'setStruct' => $setStruct,
        ]);

        $this->cache->invalidateTags([
            'user-preference-count-' . $setStruct->userId,
            'user-preference-' . $setStruct->userId . '-' . $setStruct->name,
        ]);

        return $this->persistenceHandler->userPreferenceHandler()->setUserPreference($setStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function countUserPreferences(int $userId): int
    {
        $cacheItem = $this->cache->getItem('ez-user-preference-count-' . $userId);

        $count = $cacheItem->get();
        if ($cacheItem->isHit()) {
            return $count;
        }

        $this->logger->logCall(__METHOD__, [
            'userId' => $userId,
        ]);

        $count = $this->persistenceHandler->userPreferenceHandler()->countUserPreferences($userId);
        $cacheItem->set($count);
        $cacheItem->tag(['user-preference-count-' . $userId]);
        $this->cache->save($cacheItem);

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserPreferenceByUserIdAndName(int $userId, string $name): UserPreference
    {
        $cacheItem = $this->cache->getItem('ez-user-preference-' . $userId . '-' . $name);

        $userPreference = $cacheItem->get();
        if ($cacheItem->isHit()) {
            return $userPreference;
        }

        $this->logger->logCall(__METHOD__, [
            'userId' => $userId,
            'name' => $name,
        ]);

        $userPreference = $this->persistenceHandler->userPreferenceHandler()->getUserPreferenceByUserIdAndName($userId, $name);

        $cacheItem->set($userPreference);
        $cacheItem->tag([
            'user-preference-' . $userId . '-' . $name,
        ]);
        $this->cache->save($cacheItem);

        return $userPreference;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserPreferences(int $userId, int $offset, int $limit): array
    {
        $this->logger->logCall(__METHOD__, [
            'userId' => $userId,
            'offset' => $offset,
            'limit' => $limit,
        ]);

        return $this->persistenceHandler->userPreferenceHandler()->loadUserPreferences($userId, $offset, $limit);
    }
}
