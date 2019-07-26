<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Notification;

use eZ\Publish\API\Repository\Events\Notification\BeforeCreateNotificationEvent as BeforeCreateNotificationEventInterface;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeCreateNotificationEvent extends BeforeEvent implements BeforeCreateNotificationEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Notification\CreateStruct */
    private $createStruct;

    /** @var \eZ\Publish\API\Repository\Values\Notification\Notification|null */
    private $notification;

    public function __construct(CreateStruct $createStruct)
    {
        $this->createStruct = $createStruct;
    }

    public function getCreateStruct(): CreateStruct
    {
        return $this->createStruct;
    }

    public function getNotification(): Notification
    {
        if (!$this->hasNotification()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasNotification() or set it by setNotification() before you call getter.', Notification::class));
        }

        return $this->notification;
    }

    public function setNotification(?Notification $notification): void
    {
        $this->notification = $notification;
    }

    public function hasNotification(): bool
    {
        return $this->notification instanceof Notification;
    }
}
