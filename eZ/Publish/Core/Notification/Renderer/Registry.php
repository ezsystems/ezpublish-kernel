<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Notification\Renderer;

use eZ\Publish\SPI\Notification\Renderer\NotificationRenderer;

class Registry
{
    protected $registry = [];

    public function addRenderer(string $alias, NotificationRenderer $notificationRenderer): void
    {
        $this->registry[$alias] = $notificationRenderer;
    }

    public function getRenderer(string $alias): NotificationRenderer
    {
        return $this->registry[$alias];
    }

    public function hasRenderer(string $alias): bool
    {
        return isset($this->registry[$alias]);
    }
}
