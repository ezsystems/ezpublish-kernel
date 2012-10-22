<?php
/**
 * File containing the SignalDispatcher class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\SignalDispatcher;

use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\SlotFactory;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * Dispatches Signals to their assigned Slots
 *
 * An instance of this class is required by each object that needs to send
 * Signals. It is recommended, that a SignalDispatcher works together with a
 * {@link SlotFactory} to get hold of the actual Slots that listen for a given
 * Signal, which it originally only knows by their identifier.
 *
 * @internal
 */
class DefaultSignalDispatcher extends SignalDispatcher
{
    /**
     * Slot factory
     *
     * @var SlotFactory
     */
    protected $factory;

    /**
     * Signal slot mapping
     *
     * @var array
     */
    protected $mapping;

    /**
     * Construct from factory
     *
     * @param SlotFactory $factory
     * @return void
     */
    public function __construct( SlotFactory $factory )
    {
        $this->factory = $factory;
    }

    /**
     * Emits the given $signal
     *
     * All assigned slots will eventually receive the $signal
     *
     * @param Signal $signal
     * @return void
     */
    public function emit( Signal $signal )
    {
        $signalName = get_class( $signal );
        if ( !isset( $this->mapping[$signalName] ) )
        {
            return;
        }

        foreach ( $this->mapping[$signalName] as $slotIdentifier )
        {
            $slot = $this->factory->getSlot( $slotIdentifier );
            $slot->receive( $signal );
        }
    }

    /**
     * Attachtes the Slot with $slotIdentifier to the signal with
     * $signalIdentifier
     *
     * @param string $signalIdentifier
     * @param string $slotIdentifier
     * @return void
     *
     * @TODO Are we sure we want to expose this method? Might lead to dynamic
     * attachements at runtime, which can lead to hard debugging. Better only
     * accept attachements during construction (config).
     */
    public function attach( $signalIdentifier, $slotIdentifier )
    {
        $this->mapping[$signalIdentifier][] = $slotIdentifier;
    }
}

