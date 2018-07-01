<?php

/**
 * File containing the SignalDispatcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

/**
 * Dispatches Signals to their assigned Slots.
 *
 * An instance of this class is required by each object that needs to send
 * Signals. It is recommended, that a SignalDispatcher works together with a
 * {@link SlotFactory} to get hold of the actual Slots that listen for a given
 * Signal, which it originally only knows by their identifier.
 *
 * @internal
 */
abstract class SignalDispatcher
{
    /**
     * Emits the given $signal.
     *
     * All assigned slots will eventually receive the $signal
     *
     * @param Signal $signal
     */
    abstract public function emit(Signal $signal);
}
