<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Notification;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification;

interface BeforeCreateNotificationEvent extends BeforeEvent
{
    public function getCreateStruct(): CreateStruct;

    public function getNotification(): Notification;

    public function setNotification(?Notification $notification): void;

    public function hasNotification(): bool;
}
