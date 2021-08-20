<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Notification\CreateStruct;
use eZ\Publish\SPI\Persistence\Notification\Handler as SPINotificationHandler;
use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\SPI\Persistence\Notification\Notification as SPINotification;
use eZ\Publish\SPI\Persistence\Notification\UpdateStruct;

/**
 * Test case for Persistence\Cache\NotificationHandler.
 */
class NotificationHandlerTest extends AbstractCacheHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function getHandlerMethodName(): string
    {
        return 'notificationHandler';
    }

    /**
     * {@inheritdoc}
     */
    public function getHandlerClassName(): string
    {
        return SPINotificationHandler::class;
    }

    /**
     * {@inheritdoc}
     */
    public function providerForUnCachedMethods(): array
    {
        $ownerId = 7;
        $notificationId = 5;
        $notification = new Notification([
            'id' => $notificationId,
            'ownerId' => $ownerId,
        ]);

        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tags, string? $key, mixed? $returnValue
        return [
            [
                'createNotification',
                [
                    new CreateStruct(['ownerId' => $ownerId]),
                ],
                [
                    ['notification_count', [$ownerId], true],
                    ['notification_pending_count', [$ownerId], true],
                ],
                null,
                [
                    'ez-nc-' . $ownerId,
                    'ez-npc-' . $ownerId,
                ],
                new SPINotification(),
            ],
            [
                'updateNotification',
                [
                    $notification,
                    new UpdateStruct(['isPending' => false]),
                ],
                [
                    ['notification', [$notificationId], true],
                    ['notification_pending_count', [$ownerId], true],
                ],
                null,
                [
                    'ez-n-' . $notificationId,
                    'ez-npc-' . $ownerId,
                ],
                new SPINotification(),
            ],
            [
                'delete',
                [
                    $notification,
                ],
                [
                    ['notification', [$notificationId], true],
                    ['notification_count', [$ownerId], true],
                    ['notification_pending_count', [$ownerId], true],
                ],
                null,
                [
                    'ez-n-' . $notificationId,
                    'ez-nc-' . $ownerId,
                    'ez-npc-' . $ownerId,
                ],
            ],
            [
                'loadUserNotifications', [$ownerId, 0, 25], null, null, null, [],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function providerForCachedLoadMethodsHit(): array
    {
        $notificationId = 5;
        $ownerId = 7;
        $notificationCount = 10;
        $notificationCountPending = 5;

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data
        return [
            [
                'countPendingNotifications',
                [
                    $ownerId,
                ],
                'ez-npc-' . $ownerId,
                [['notification_pending_count', [$ownerId], true]],
                ['ez-npc-' . $ownerId],
                $notificationCount,
            ],
            [
                'countNotifications',
                [
                    $ownerId,
                ],
                'ez-nc-' . $ownerId,
                [['notification_count', [$ownerId], true]],
                ['ez-nc-' . $ownerId],
                $notificationCountPending,
            ],
            [
                'getNotificationById',
                [
                    $notificationId,
                ],
                'ez-n-' . $notificationId,
                [['notification', [$notificationId], true]],
                ['ez-n-' . $notificationId],
                new SPINotification(['id' => $notificationId]),
            ],
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        $notificationId = 5;
        $ownerId = 7;
        $notificationCount = 10;
        $notificationCountPending = 5;

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data
        return [
            [
                'countPendingNotifications',
                [
                    $ownerId,
                ],
                'ez-npc-' . $ownerId,
                [['notification_pending_count', [$ownerId], true]],
                ['ez-npc-' . $ownerId],
                $notificationCount,
            ],
            [
                'countNotifications',
                [
                    $ownerId,
                ],
                'ez-nc-' . $ownerId,
                [['notification_count', [$ownerId], true]],
                ['ez-nc-' . $ownerId],
                $notificationCountPending,
            ],
            [
                'getNotificationById',
                [
                    $notificationId,
                ],
                'ez-n-' . $notificationId,
                [['notification', [$notificationId], true]],
                ['ez-n-' . $notificationId],
                new SPINotification(['id' => $notificationId]),
            ],
        ];
    }
}
