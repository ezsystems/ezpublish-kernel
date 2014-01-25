<?php
/**
 * File containing the NullSlotFactory class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\SignalSlot\SlotFactory;

use eZ\Publish\Core\Repository\SignalSlot\SlotFactory;
use RuntimeException;

class NullSlotFactory extends SlotFactory
{
    /**
     * Returns a Slot with the given $slotIdentifier
     *
     * @param string $slotIdentifier
     *
     * @return \eZ\Publish\Core\Repository\SignalSlot\Slot
     */
    public function getSlot( $slotIdentifier )
    {
        throw new RuntimeException( "Slot creation not supported." );
    }
}
