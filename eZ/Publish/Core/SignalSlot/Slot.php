<?php

/**
 * File containing the SignalDispatcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

/**
 * A Slot can be assigned to receive a certain Signal.
 */
abstract class Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param Signal $signal
     */
    abstract public function receive(Signal $signal);
}
