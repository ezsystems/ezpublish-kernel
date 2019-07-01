<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Notification;

use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\Core\Event\AfterEvent;

final class CreateNotificationEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Notification\Notification */
    private $notification;

    /** @var \eZ\Publish\API\Repository\Values\Notification\CreateStruct */
    private $createStruct;

    public function __construct(
        Notification $notification,
        CreateStruct $createStruct
    ) {
        $this->notification = $notification;
        $this->createStruct = $createStruct;
    }

    public function getNotification(): Notification
    {
        return $this->notification;
    }

    public function getCreateStruct(): CreateStruct
    {
        return $this->createStruct;
    }
}
