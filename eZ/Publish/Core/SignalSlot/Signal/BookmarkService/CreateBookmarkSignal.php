<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot\Signal\BookmarkService;

use eZ\Publish\Core\SignalSlot\Signal;

class CreateBookmarkSignal extends Signal
{
    /**
     * Location ID.
     *
     * @var int
     */
    public $locationId;
}
