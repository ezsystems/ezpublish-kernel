<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Notification\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Signal\NotificationService\NotificationSignal;
use eZ\Publish\Core\SignalSlot\Slot;
use eZ\Publish\SPI\Persistence\Notification\Handler;
use eZ\Publish\SPI\Persistence\Notification\Notification;

class OnNotificationSlot extends Slot
{
    /** @var Handler $persistenceHandler */
    protected $persistenceHandler;

    /**
     * OnNotificationSlot constructor.
     *
     * @param Handler $persistenceHandler
     */
    public function __construct(Handler $persistenceHandler)
    {
        $this->persistenceHandler = $persistenceHandler;
    }

    /**
     * Receive the given $signal and react on it.
     *
     * @param Signal $signal
     * @return bool
     */
    public function receive(Signal $signal)
    {
        if (!($signal instanceof NotificationSignal)) {
            return false;
        }

        $notification = new Notification();

        $notification->ownerId = $signal->ownerId;
        $notification->type = $signal->type;
        $notification->data = $signal->data;
        $notification->created = time();
        $notification->isPending = true;

        $this->persistenceHandler->createNotification($notification);

        return true;
    }
}
