<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\NotificationService as APINotificationService;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\API\Repository\Values\Notification\NotificationList;
use eZ\Publish\Core\SignalSlot\NotificationService;
use eZ\Publish\Core\SignalSlot\Signal\NotificationService\NotificationCreateSignal;
use eZ\Publish\Core\SignalSlot\Signal\NotificationService\NotificationDeleteSignal;
use eZ\Publish\Core\SignalSlot\Signal\NotificationService\NotificationReadSignal;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;

class NotificationServiceTest extends ServiceTest
{
    public function serviceProvider()
    {
        $notificationId = 5;

        $notification = new Notification([
            'id' => $notificationId,
        ]);

        $createStruct = new CreateStruct([
            'ownerId' => 10,
            'type' => 'Foo',
            'data' => [],
        ]);

        return [
            [
                'loadNotifications',
                [0, 25],
                new NotificationList(),
                0,
            ],
            [
                'getNotification',
                [$notificationId],
                new Notification(),
                0,
            ],
            [
                'markNotificationAsRead',
                [$notification],
                null,
                1,
                NotificationReadSignal::class,
                [
                    'notificationId' => $notificationId,
                ],
            ],
            [
                'getPendingNotificationCount',
                [],
                10,
                0,
            ],
            [
                'deleteNotification',
                [$notification],
                null,
                1,
                NotificationDeleteSignal::class,
                [
                    'notificationId' => $notificationId,
                ],
            ],
            [
                'createNotification',
                [$createStruct],
                $notification,
                1,
                NotificationCreateSignal::class,
                [
                    'ownerId' => $createStruct->ownerId,
                    'type' => $createStruct->type,
                    'data' => $createStruct->data,
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getServiceMock()
    {
        return $this->createMock(APINotificationService::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSignalSlotService($innerService, SignalDispatcher $dispatcher)
    {
        return new NotificationService($innerService, $dispatcher);
    }
}
