<?php
/**
 * File containing the GeneralSlotFactory class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\SlotFactory;

use eZ\Publish\Core\SignalSlot\Slot;
use eZ\Publish\Core\SignalSlot\SlotFactory;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * Slot factory that is able to lookup slots based on identifier.
 *
 * @deprecated Slot factories are not needed any more.
 */
class GeneralSlotFactory extends SlotFactory
{
    /**
     * @var \eZ\Publish\Core\SignalSlot\Slot[]
     */
    protected $slots = array();

    /**
     * @param \eZ\Publish\Core\SignalSlot\Slot[] $slots
     */
    public function __construct( array $slots = array() )
    {
        $this->slots = $slots;
    }

    /**
     * Registers a new Slot by its identifier.
     *
     * @param string $slotIdentifier
     * @param Slot $slot
     */
    public function addSlot( $slotIdentifier, Slot $slot )
    {
        $this->slots[$slotIdentifier] = $slot;
    }

    /**
     * Returns a Slot with the given $slotIdentifier
     *
     * @param string $slotIdentifier
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException When no slot is found
     *
     * @return \eZ\Publish\Core\SignalSlot\Slot
     */
    public function getSlot( $slotIdentifier )
    {
        if ( !isset( $this->slots[$slotIdentifier] ) )
            throw new NotFoundException( 'slot', $slotIdentifier );

        return $this->slots[$slotIdentifier];
    }
}
