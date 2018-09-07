<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\UserPreference\UserPreference;
use eZ\Publish\API\Repository\Values\UserPreference\UserPreferenceList;

/**
 * User Preference Service.
 *
 * This service provides methods for managing user preferences. It works in the context of a current User (obtained from the PermissionResolver).
 */
interface UserPreferenceService
{
    /**
     * Set user preference.
     *
     * @param \eZ\Publish\API\Repository\Values\UserPreference\UserPreferenceSetStruct[] $userPreferenceSetStructs
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to set user preference
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If the $userPreferenceSetStruct is invalid
     */
    public function setUserPreference(array $userPreferenceSetStructs): void;

    /**
     * Get currently logged user preference by key.
     *
     * @param string $userPreferenceName
     *
     * @return \eZ\Publish\API\Repository\Values\UserPreference\UserPreference
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the current user user is not allowed to fetch user preference
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function getUserPreference(string $userPreferenceName): UserPreference;

    /**
     * Get currently logged user preferences.
     *
     * @param int $offset the start offset for paging
     * @param int $limit the number of user preferences returned
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return \eZ\Publish\API\Repository\Values\UserPreference\UserPreferenceList
     */
    public function loadUserPreferences(int $offset = 0, int $limit = 25): UserPreferenceList;

    /**
     * Get count of total preferences for currently logged user.
     *
     * @return int
     */
    public function getUserPreferenceCount(): int;
}
