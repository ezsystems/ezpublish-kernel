<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Notification\Slot;

use eZ\Publish\API\Repository\NotificationService;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Signal\NotificationService\NotificationCreateSignal;
use eZ\Publish\Core\SignalSlot\Slot;

class OnNotificationSlot extends Slot
{
    /** @var \eZ\Publish\API\Repository\NotificationService $notificationService */
    protected $notificationService;

    /**
     * OnNotificationSlot constructor.
     *
     * @param \eZ\Publish\API\Repository\NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return bool
     */
    public function receive(Signal $signal)
    {
        if (!($signal instanceof NotificationCreateSignal)) {
            return false;
        }

        $notification = new CreateStruct();

        $notification->ownerId = $signal->ownerId;
        $notification->type = $signal->type;
        $notification->data = $signal->data;

        $this->notificationService->createNotification($notification);

        return true;
    }
}
