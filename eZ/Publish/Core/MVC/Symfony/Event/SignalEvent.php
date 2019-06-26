<?php

/**
 * File containing the SignalEvent class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Event;

use eZ\Publish\Core\SignalSlot\Signal;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is sent whenever a Signal is emitted by SignalSlot repository services.
 * It contains the Signal object.
 */
class SignalEvent extends Event
{
    /** @var \eZ\Publish\Core\SignalSlot\Signal */
    private $signal;

    public function __construct(Signal $signal)
    {
        $this->signal = $signal;
    }

    /**
     * @return Signal
     */
    public function getSignal()
    {
        return $this->signal;
    }
}
