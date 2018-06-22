<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Notification;

use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use eZ\Publish\SPI\Persistence\Bookmark\CreateStruct;
use eZ\Publish\SPI\Persistence\Notification\Notification;
use eZ\Publish\SPI\Persistence\Notification\UpdateStruct;

class Mapper
{
    /**
     * Extracts Bookmark objects from $rows.
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification[]
     */
    public function extractNotificationsFromRows(array $rows): array
    {
        $notifications = [];
        foreach ($rows as $row) {
            $notifications[] = $this->extractNotificationFromRow($row);
        }

        return $notifications;
    }

    /**
     * Extract Bookmark object from $row.
     *
     * @param array $row
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    private function extractNotificationFromRow(array $row): Notification
    {
        $notification = new Notification();
        $notification->id = (int)$row['id'];
        $notification->ownerId = (int)$row['owner_id'];
        $notification->type = $row['type'];
        $notification->created = (int)$row['created'];
        $notification->isPending = $row['is_pending'];
        $notification->data = json_decode($row['data']);

        return $notification;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Notification\UpdateStruct $updateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function createNotificationFromUpdateStruct(UpdateStruct $updateStruct): Notification
    {
        $notification = new Notification();
        $notification->isPending = $updateStruct->isPending;

        return $notification;
    }
}
