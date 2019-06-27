<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\UserPreferenceService as UserPreferenceServiceInterface;
use eZ\Publish\API\Repository\Values\UserPreference\UserPreference;
use eZ\Publish\API\Repository\Values\UserPreference\UserPreferenceList;
use eZ\Publish\Core\SignalSlot\Signal\UserPreferenceService\UserPreferenceSetSignal;

class UserPreferenceService implements UserPreferenceServiceInterface
{
    /** @var \eZ\Publish\API\Repository\UserPreferenceService */
    protected $service;

    /** @var \eZ\Publish\Core\SignalSlot\SignalDispatcher */
    protected $signalDispatcher;

    /**
     * @param \eZ\Publish\API\Repository\UserPreferenceService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(UserPreferenceServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserPreference(array $userPreferenceSetStructs): void
    {
        $this->service->setUserPreference($userPreferenceSetStructs);

        foreach ($userPreferenceSetStructs as $userPreferenceSetStruct) {
            $this->signalDispatcher->emit(new UserPreferenceSetSignal([
                'name' => $userPreferenceSetStruct->name,
                'value' => $userPreferenceSetStruct->value,
            ]));
        }
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
