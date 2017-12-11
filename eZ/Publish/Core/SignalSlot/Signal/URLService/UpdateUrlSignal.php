<?php

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
}
