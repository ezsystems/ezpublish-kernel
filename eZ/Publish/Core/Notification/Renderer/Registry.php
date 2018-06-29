<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Notification\Renderer;

class Registry
{
    /** @var \eZ\Publish\Core\Notification\Renderer\NotificationRenderer[] */
    protected $registry = [];

    /**
     * @param string $alias
     * @param \eZ\Publish\Core\Notification\Renderer\NotificationRenderer $notificationRenderer
     */
    public function addRenderer(string $alias, NotificationRenderer $notificationRenderer): void
    {
        $this->registry[$alias] = $notificationRenderer;
    }

    /**
     * @param string $alias
     *
     * @return \eZ\Publish\Core\Notification\Renderer\NotificationRenderer
     */
    public function getRenderer(string $alias): NotificationRenderer
    {
        return $this->registry[$alias];
    }

    /**
     * @param string $alias
     *
     * @return bool
     */
    public function hasRenderer(string $alias): bool
    {
        return isset($this->registry[$alias]);
    }
}
