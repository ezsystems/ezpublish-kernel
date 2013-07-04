<?php
/**
 * File containing the SignalDispatcher class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
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
    protected $mapping = array();

    /**
     * Construct from factory
     *
     * @param \eZ\Publish\Core\SignalSlot\SlotFactory $factory
     * @param array $mapping
     */
    public function __construct( SlotFactory $factory, array $mapping = array() )
    {
        $this->factory = $factory;
        $this->mapping = $mapping;
        if ( !isset( $this->mapping['*'] ) )
        {
            $this->mapping['*'] = array();
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
        if ( !isset( $this->mapping[$signalName] ) )
        {
            $this->mapping[$signalName] = array();
        }

        foreach ( array_merge( $this->mapping['*'], $this->mapping[$signalName] ) as $slotIdentifier )
        {
            $slot = $this->factory->getSlot( $slotIdentifier );
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
     * @param string $slotIdentifier
     */
    public function attach( $signalIdentifier, $slotIdentifier )
    {
        if ( $signalIdentifier[0] === '\\' )
        {
            $signalIdentifier = substr( $signalIdentifier, 1 );
        }
        else if ( $signalIdentifier !== '*' )
        {
            $signalIdentifier = static::RELATIVE_SIGNAL_NAMESPACE . "\\$signalIdentifier";
        }

        $this->mapping[$signalIdentifier][] = $slotIdentifier;
    }
}
