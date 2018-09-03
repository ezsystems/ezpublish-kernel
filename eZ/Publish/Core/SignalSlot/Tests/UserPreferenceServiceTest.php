<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\UserPreferenceService as APIUserPreferenceService;
use eZ\Publish\API\Repository\Values\UserPreference\UserPreferenceSetStruct;
use eZ\Publish\API\Repository\Values\UserPreference\UserPreferenceList;
use eZ\Publish\Core\SignalSlot\UserPreferenceService;
use eZ\Publish\Core\SignalSlot\Signal\UserPreferenceService\UserPreferenceSetSignal;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;

class UserPreferenceServiceTest extends ServiceTest
{
    public function serviceProvider()
    {
        $setStruct = new UserPreferenceSetStruct([
            'name' => 'timezone',
            'value' => 'America/New_York',
        ]);

        return [
            [
                'loadUserPreferences',
                [0, 25],
                new UserPreferenceList(),
                0,
            ],
            [
                'setUserPreference',
                [[$setStruct]],
                null,
                1,
                UserPreferenceSetSignal::class,
                [
                    'name' => $setStruct->name,
                    'value' => $setStruct->value,
                ],
            ],
            [
                'getUserPreferenceCount',
                [],
                10,
                0,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getServiceMock()
    {
        return $this->createMock(APIUserPreferenceService::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSignalSlotService($innerService, SignalDispatcher $dispatcher)
    {
        return new UserPreferenceService($innerService, $dispatcher);
    }
}
