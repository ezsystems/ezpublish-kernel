<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Notification;

final class NotificationEvents
{
    public const MARK_NOTIFICATION_AS_READ = MarkNotificationAsReadEvent::NAME;
    public const BEFORE_MARK_NOTIFICATION_AS_READ = BeforeMarkNotificationAsReadEvent::NAME;
    public const CREATE_NOTIFICATION = CreateNotificationEvent::NAME;
    public const BEFORE_CREATE_NOTIFICATION = BeforeCreateNotificationEvent::NAME;
    public const DELETE_NOTIFICATION = DeleteNotificationEvent::NAME;
    public const BEFORE_DELETE_NOTIFICATION = BeforeDeleteNotificationEvent::NAME;
}
