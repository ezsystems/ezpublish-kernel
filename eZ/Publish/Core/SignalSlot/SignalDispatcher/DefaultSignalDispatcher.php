<?php

/**
 * File containing the SignalDispatcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\SignalDispatcher;

use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\Slot;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * Dispatches Signals to their assigned Slots.
 *
 * An instance of this class is required by each object that needs to send
 * Signals.
 *
 * @internal
 */
class DefaultSignalDispatcher extends SignalDispatcher
{
    /**
     * Relative namespace for internal signals.
     */
    const RELATIVE_SIGNAL_NAMESPACE = 'eZ\\Publish\\Core\\SignalSlot\\Signal';

    /**
     * Slot factory.
     *
     * @var \eZ\Publish\Core\SignalSlot\SlotFactory
     */
    protected $factory;

    /**
     * Signal slot mapping.
     * '*' signal name stands for "every signals". All slots registered to it will be triggered each time a signal is emitted.
     *
     * @var array
     */
    protected $signalSlotMap = [];

    /**
     * Construct from factory.
     *
     * @param array $signalSlotMap
     */
    public function __construct(array $signalSlotMap = [])
    {
        $this->signalSlotMap = $signalSlotMap;
        if (!isset($this->signalSlotMap['*'])) {
            $this->signalSlotMap['*'] = [];
        }
    }

    /**
     * Emits the given $signal.
     *
     * All assigned slots will eventually receive the $signal
     *
     * @param Signal $signal
     */
    public function emit(Signal $signal)
    {
        $signalName = get_class($signal);
        if (!isset($this->signalSlotMap[$signalName])) {
            $this->signalSlotMap[$signalName] = [];
        }

        foreach (array_merge($this->signalSlotMap['*'], $this->signalSlotMap[$signalName]) as $slot) {
            /* @var \eZ\Publish\Core\SignalSlot\Slot $slot */
            $slot->receive($signal);
        }
    }

    /**
     * Attaches the Slot with $slotIdentifier to the signal with
     * $signalIdentifier.
     *
     *
     * @param string $signalIdentifier
     * @param \eZ\Publish\Core\SignalSlot\Slot $slot
     *
     * @deprecated pass signal slots directly to the constructor ({@see __construct()})
     */
    public function attach($signalIdentifier, Slot $slot)
    {
        if ($signalIdentifier[0] === '\\') {
            $signalIdentifier = substr($signalIdentifier, 1);
        } elseif ($signalIdentifier !== '*') {
            $signalIdentifier = static::RELATIVE_SIGNAL_NAMESPACE . "\\$signalIdentifier";
        }

        $this->signalSlotMap[$signalIdentifier][] = $slot;
    }
}
