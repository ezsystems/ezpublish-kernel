<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\UserPreference;

use eZ\Publish\SPI\Persistence\UserPreference\UserPreferenceSetStruct;

abstract class Gateway
{
    /**
     * Store UserPreference ValueObject in persistent storage.
     *
     * @param \eZ\Publish\SPI\Persistence\UserPreference\UserPreferenceSetStruct $userPreference
     *
     * @return int
     */
    abstract public function setUserPreference(UserPreferenceSetStruct $userPreference): int;

    /**
     * Get UserPreference by its user ID and name.
     *
     * @param int $userId
     * @param string $name
     *
     * @return array
     */
    abstract public function getUserPreferenceByUserIdAndName(int $userId, string $name): array;

    /**
     * @param int $userId
     *
     * @return int
     */
    abstract public function countUserPreferences(int $userId): int;

    /**
     * @param int $userId
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    abstract public function loadUserPreferences(int $userId, int $offset = 0, int $limit = -1): array;
}
