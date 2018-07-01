<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\URLService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * Signal emitted when URL is updated.
 */
class UpdateUrlSignal extends Signal
{
    /**
     * URL ID.
     *
     * @var int
     */
    public $urlId;

    /**
     * If URL address was changed.
     *
     * @var bool
     *
     * @since 6.13.2
     */
    public $urlChanged;
}
