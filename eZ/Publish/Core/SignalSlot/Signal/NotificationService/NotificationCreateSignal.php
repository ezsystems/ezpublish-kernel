<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot\Signal\NotificationService;

use eZ\Publish\Core\SignalSlot\Signal;

class NotificationCreateSignal extends Signal
{
    /** @var int $ownerId */
    public $ownerId;

    /** @var string $type */
    public $type;

    /** @var array $data */
    public $data;
}
