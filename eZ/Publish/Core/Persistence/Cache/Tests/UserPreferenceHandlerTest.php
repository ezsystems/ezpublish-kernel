<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Tests\Core\Persistence\Cache;

use eZ\Publish\Core\Persistence\Cache\Tests\AbstractCacheHandlerTest;
use eZ\Publish\SPI\Persistence\UserPreference\UserPreferenceSetStruct;
use eZ\Publish\SPI\Persistence\UserPreference\Handler as SPIUserPreferenceHandler;
use eZ\Publish\SPI\Persistence\UserPreference\UserPreference as SPIUserPreference;

/**
 * Test case for Persistence\Cache\UserPreferenceHandler.
 */
class UserPreferenceHandlerTest extends AbstractCacheHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function getHandlerMethodName(): string
    {
        return 'userPreferenceHandler';
    }

    /**
     * {@inheritdoc}
     */
    public function getHandlerClassName(): string
    {
        return SPIUserPreferenceHandler::class;
    }

    /**
     * {@inheritdoc}
     */
    public function providerForUnCachedMethods(): array
    {
        $userId = 7;
        $name = 'setting';

        // string $method, array $arguments, array? $tags, string? $key, mixed? $returnValue
        return [
            [
                'setUserPreference',
                [
                    new UserPreferenceSetStruct([
                        'userId' => $userId,
                        'name' => $name,
                    ]),
                ],
                [
                    'user-preference-count-' . $userId,
                    'user-preference-' . $userId . '-' . $name,
                ],
                null,
                new SPIUserPreference(),
            ],
            [
                'loadUserPreferences', [$userId, 0, 25], null, null, [],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function providerForCachedLoadMethods(): array
    {
        $userId = 7;
        $name = 'setting';
        $userPreferenceCount = 10;

        // string $method, array $arguments, string $key, mixed? $data
        return [
            [
                'countUserPreferences',
                [
                    $userId,
                ],
                'ez-user-preference-count-' . $userId,
                $userPreferenceCount,
            ],
            [
                'getUserPreferenceByUserIdAndName',
                [
                    $userId,
                    $name,
                ],
                'ez-user-preference-' . $userId . '-' . $name,
                new SPIUserPreference(['userId' => $userId, 'name' => $name]),
            ],
        ];
    }
}
