<?php

/**
 * File containing the SignalDispatcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

/**
 * Fetches (creates) Slots by their identifier.
 *
 * @internal
 */
abstract class SlotFactory
{
    /**
     * Returns a Slot with the given $slotIdentifier.
     *
     * @param string $slotIdentifier
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException When no slot is found
     *
     * @return \eZ\Publish\Core\SignalSlot\Slot
     */
    abstract public function getSlot($slotIdentifier);
}
