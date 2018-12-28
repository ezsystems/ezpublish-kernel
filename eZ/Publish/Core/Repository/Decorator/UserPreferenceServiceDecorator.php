<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\UserPreferenceService;
use eZ\Publish\API\Repository\Values\UserPreference\UserPreference;
use eZ\Publish\API\Repository\Values\UserPreference\UserPreferenceList;

abstract class UserPreferenceServiceDecorator implements UserPreferenceService
{
    /**
     * @var \eZ\Publish\API\Repository\UserPreferenceService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\UserPreferenceService $service
     */
    public function __construct(UserPreferenceService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserPreference(array $userPreferenceSetStructs): void
    {
        $this->service->setUserPreference($userPreferenceSetStructs);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserPreference(string $userPreferenceName): UserPreference
    {
        return $this->service->getUserPreference($userPreferenceName);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserPreferences(int $offset = 0, int $limit = 25): UserPreferenceList
    {
        return $this->service->loadUserPreferences($offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserPreferenceCount(): int
    {
        return $this->service->getUserPreferenceCount();
    }
}
