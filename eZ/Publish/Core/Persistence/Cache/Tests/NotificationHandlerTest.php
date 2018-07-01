<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Tests\Core\Persistence\Cache;

use eZ\Publish\Core\Persistence\Cache\Tests\AbstractCacheHandlerTest;
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

        // string $method, array $arguments, array? $tags, string? $key, mixed? $returnValue
        return [
            [
                'createNotification',
                [
                    new CreateStruct(['ownerId' => $ownerId]),
                ],
                [
                    'notification-count-' . $ownerId,
                    'notification-pending-count-' . $ownerId,
                ],
                null,
                new SPINotification(),
            ],
            [
                'updateNotification',
                [
                    $notification,
                    new UpdateStruct(['isPending' => false]),
                ],
                [
                    'notification-' . $notificationId,
                    'notification-pending-count-' . $ownerId,
                ],
                null,
                new SPINotification(),
            ],
            [
                'delete',
                [
                    $notification,
                ],
                [
                    'notification-' . $notificationId,
                    'notification-count-' . $ownerId,
                    'notification-pending-count-' . $ownerId,
                ],
            ],
            [
                'loadUserNotifications', [$ownerId, 0, 25], null, null, [],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function providerForCachedLoadMethods(): array
    {
        $notificationId = 5;
        $ownerId = 7;
        $notificationCount = 10;
        $notificationCountPending = 5;

        // string $method, array $arguments, string $key, mixed? $data
        return [
            [
                'countPendingNotifications',
                [
                    $ownerId,
                ],
                'ez-notification-pending-count-' . $ownerId,
                $notificationCount,
            ],
            [
                'countNotifications',
                [
                    $ownerId,
                ],
                'ez-notification-count-' . $ownerId,
                $notificationCountPending,
            ],
            [
                'getNotificationById',
                [
                    $notificationId,
                ],
                'ez-notification-' . $notificationId,
                new SPINotification(['id' => $notificationId]),
            ],
        ];
    }
}
