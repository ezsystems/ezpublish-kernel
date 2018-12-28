<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\UserPreferenceService as UserPreferenceServiceInterface;
use eZ\Publish\Core\Repository\Decorator\UserPreferenceServiceDecorator;
use eZ\Publish\Core\SignalSlot\Signal\UserPreferenceService\UserPreferenceSetSignal;

class UserPreferenceService extends UserPreferenceServiceDecorator
{
    /**
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * @param \eZ\Publish\API\Repository\UserPreferenceService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(UserPreferenceServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        parent::__construct($service);

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
}
