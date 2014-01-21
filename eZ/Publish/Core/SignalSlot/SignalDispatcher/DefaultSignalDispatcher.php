<?php
/**
 * File containing the SignalDispatcher class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\SignalDispatcher;

use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\Slot;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * Dispatches Signals to their assigned Slots
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
     * Slot factory
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
    protected $signalSlotMap = array();

    /**
     * Construct from factory
     *
     * @param array $signalSlotMap
     */
    public function __construct( array $signalSlotMap = array() )
    {
        $this->signalSlotMap = $signalSlotMap;
        if ( !isset( $this->signalSlotMap['*'] ) )
        {
            $this->signalSlotMap['*'] = array();
        }
    }

    /**
     * Emits the given $signal
     *
     * All assigned slots will eventually receive the $signal
     *
     * @param Signal $signal
     *
     * @return void
     */
    public function emit( Signal $signal )
    {
        $signalName = get_class( $signal );
        if ( !isset( $this->signalSlotMap[$signalName] ) )
        {
            $this->signalSlotMap[$signalName] = array();
        }

        foreach ( array_merge( $this->signalSlotMap['*'], $this->signalSlotMap[$signalName] ) as $slot )
        {
            /** @var \eZ\Publish\Core\SignalSlot\Slot $slot */
            $slot->receive( $signal );
        }
    }

    /**
     * Attaches the Slot with $slotIdentifier to the signal with
     * $signalIdentifier
     *
     * @access private For unit test use.
     *
     * @param string $signalIdentifier
     * @param \eZ\Publish\Core\SignalSlot\Slot $slot
     */
    public function attach( $signalIdentifier, Slot $slot )
    {
        if ( $signalIdentifier[0] === '\\' )
        {
            $signalIdentifier = substr( $signalIdentifier, 1 );
        }
        else if ( $signalIdentifier !== '*' )
        {
            $signalIdentifier = static::RELATIVE_SIGNAL_NAMESPACE . "\\$signalIdentifier";
        }

        $this->signalSlotMap[$signalIdentifier][] = $slot;
    }
}
