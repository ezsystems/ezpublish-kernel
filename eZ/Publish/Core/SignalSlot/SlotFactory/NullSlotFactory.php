<?php

/**
 * File containing the NullSlotFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\SlotFactory;

use eZ\Publish\Core\SignalSlot\SlotFactory;
use RuntimeException;

class NullSlotFactory extends SlotFactory
{
    /**
     * Returns a Slot with the given $slotIdentifier.
     *
     * @param string $slotIdentifier
     *
     * @return \eZ\Publish\Core\SignalSlot\Slot
     */
    public function getSlot($slotIdentifier)
    {
        throw new RuntimeException('Slot creation not supported.');
    }
}
