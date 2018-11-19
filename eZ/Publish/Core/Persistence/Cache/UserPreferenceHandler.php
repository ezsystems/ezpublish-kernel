<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
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
     * Constant used for storing not found results for getUserPreferenceByUserIdAndName().
     */
    private const NOT_FOUND = 'NotFoundException';

    /**
     * {@inheritdoc}
     */
    public function setUserPreference(UserPreferenceSetStruct $setStruct): UserPreference
    {
        $this->logger->logCall(__METHOD__, [
            'setStruct' => $setStruct,
        ]);

        $this->cache->deleteItems([
            'ez-user-preference-count-' . $setStruct->userId,
            'ez-user-preference-' . $setStruct->userId . '-' . $setStruct->name,
        ]);

        return $this->persistenceHandler->userPreferenceHandler()->setUserPreference($setStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function countUserPreferences(int $userId): int
    {
        $cacheItem = $this->cache->getItem('ez-user-preference-count-' . $userId);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, [
            'userId' => $userId,
        ]);

        $count = $this->persistenceHandler->userPreferenceHandler()->countUserPreferences($userId);
        $cacheItem->set($count);
        $cacheItem->tag(['user-preference-' . $userId]);
        $this->cache->save($cacheItem);

        return $count;
    }

    /**
     * {@inheritdoc}
     *
     * Needs to store NotFoundExceptions as UserPreference feature heavily uses this in valid lookups.
     */
    public function getUserPreferenceByUserIdAndName(int $userId, string $name): UserPreference
    {
        $cacheItem = $this->cache->getItem('ez-user-preference-' . $userId . '-' . $name);
        if ($cacheItem->isHit()) {
            $userPreference = $cacheItem->get();
            if ($userPreference === self::NOT_FOUND) {
                throw new NotFoundException('User Preference', $userId . ',' . $name);
            }

            return $userPreference;
        }

        $this->logger->logCall(__METHOD__, [
            'userId' => $userId,
            'name' => $name,
        ]);
        $cacheItem->tag(['user-preference-' . $userId]);

        try {
            $userPreference = $this->persistenceHandler->userPreferenceHandler()->getUserPreferenceByUserIdAndName(
                $userId,
                $name
            );
        } catch (APINotFoundException $e) {
            $cacheItem->set(self::NOT_FOUND);
            $this->cache->save($cacheItem);
            throw new NotFoundException('User Preference', $userId . ',' . $name, $e);
        }

        $cacheItem->set($userPreference);
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
