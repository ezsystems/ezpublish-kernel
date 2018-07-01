<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Notification\Renderer;

use eZ\Publish\API\Repository\Values\Notification\Notification;

interface NotificationRenderer
{
    /**
     * @param \eZ\Publish\API\Repository\Values\Notification\Notification $notification
     *
     * @return string
     */
    public function render(Notification $notification): string;

    /**
     * @param \eZ\Publish\API\Repository\Values\Notification\Notification $notification
     *
     * @return string|null
     */
    public function generateUrl(Notification $notification): ?string;
}
