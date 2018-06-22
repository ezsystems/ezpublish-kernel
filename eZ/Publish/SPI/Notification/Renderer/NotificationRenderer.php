<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Notification\Renderer;

use eZ\Publish\API\Repository\Values\Notification\Notification;

interface NotificationRenderer
{
    public function render(Notification $notification): string;

    public function generateUrl(Notification $notification): ?string;
}
