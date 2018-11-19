<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\UserPreference;

interface Handler
{
    /**
     * Store UserPreference ValueObject in persistent storage.
     *
     * @param \eZ\Publish\SPI\Persistence\UserPreference\UserPreferenceSetStruct $setStruct
     *
     * @return \eZ\Publish\SPI\Persistence\UserPreference\UserPreference
     */
    public function setUserPreference(UserPreferenceSetStruct $setStruct): UserPreference;

    /**
     * Get UserPreference by its user ID and name.
     *
     * @param int $userId
     * @param string $name
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no value is found for given preference name.
     *
     * @return \eZ\Publish\SPI\Persistence\UserPreference\UserPreference
     */
    public function getUserPreferenceByUserIdAndName(int $userId, string $name): UserPreference;

    /**
     * @param int $userId
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\SPI\Persistence\UserPreference\UserPreference[]
     */
    public function loadUserPreferences(int $userId, int $offset, int $limit): array;

    /**
     * @param int $userId
     *
     * @return int
     */
    public function countUserPreferences(int $userId): int;
}
