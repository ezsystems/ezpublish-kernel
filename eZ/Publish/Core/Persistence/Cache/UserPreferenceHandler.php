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
class UserPreferenceHandler extends AbstractInMemoryPersistenceHandler implements Handler
{
    private const PREFIXED_USER_PREFERENCE_TAG = 'prefixed_user_preference';
    private const PREFIXED_USER_PREFERENCE_WITH_SUFFIX_TAG = 'prefixed_user_preference_with_suffix';

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
            $this->tagGenerator->generate(
                self::PREFIXED_USER_PREFERENCE_WITH_SUFFIX_TAG,
                [$setStruct->userId, $setStruct->name]
            ),
        ]);

        return $this->persistenceHandler->userPreferenceHandler()->setUserPreference($setStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function countUserPreferences(int $userId): int
    {
        $this->logger->logCall(__METHOD__, [
            'userId' => $userId,
        ]);

        return $this->persistenceHandler->userPreferenceHandler()->countUserPreferences($userId);
    }

    /**
     * {@inheritdoc}
     *
     * Needs to store NotFoundExceptions as UserPreference feature heavily uses this in valid lookups.
     */
    public function getUserPreferenceByUserIdAndName(int $userId, string $name): UserPreference
    {
        $tagGenerator = $this->tagGenerator;

        $userPreference = $this->getCacheValue(
            $userId,
            $tagGenerator->generate(self::PREFIXED_USER_PREFERENCE_TAG, [], true),
            function ($userId) use ($name) {
                try {
                    return $this->persistenceHandler->userPreferenceHandler()->getUserPreferenceByUserIdAndName(
                        $userId,
                        $name
                    );
                } catch (APINotFoundException $e) {
                    return self::NOT_FOUND;
                }
            },
            static function () {
                return [];
            },
            static function () use ($userId, $name, $tagGenerator) {
                return [
                    $tagGenerator->generate(
                        self::PREFIXED_USER_PREFERENCE_WITH_SUFFIX_TAG,
                        [$userId, $name]
                    ),
                ];
            },
            '-' . $name
        );

        if ($userPreference === self::NOT_FOUND) {
            throw new NotFoundException('User Preference', $userId . ',' . $name);
        }

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
