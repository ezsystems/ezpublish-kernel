<?php
/**
 * File containing the GeneralSlotFactory class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\SlotFactory;
use eZ\Publish\Core\SignalSlot\SlotFactory;

/**
 * Slot factory that is able to lookup slots based on identifier.
 *
 * @deprecated To be removed when unit test runs on Sf stack, and ContainerSlotFactory is used everywhere.
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
     * Returns a Slot with the given $slotIdentifier
     *
     * @param string $slotIdentifier
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException When no slot is found
     * @return \eZ\Publish\Core\SignalSlot\Slot
     */
    public function getSlot( $slotIdentifier )
    {
        if ( !isset( $this->slots[$slotIdentifier] ) )
            throw new \eZ\Publish\Core\Base\Exceptions\NotFoundException( 'slot', $slotIdentifier );

        return $this->slots[$slotIdentifier];
    }
}
