<?php

/**
 * File containing the GeneralSlotFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
    /** @var \eZ\Publish\Core\SignalSlot\Slot[] */
    protected $slots = [];

    /**
     * @param \eZ\Publish\Core\SignalSlot\Slot[] $slots
     */
    public function __construct(array $slots = [])
    {
        $this->slots = $slots;
    }

    /**
     * Returns a Slot with the given $slotIdentifier.
     *
     * @param string $slotIdentifier
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException When no slot is found
     *
     * @return \eZ\Publish\Core\SignalSlot\Slot
     */
    public function getSlot($slotIdentifier)
    {
        if (!isset($this->slots[$slotIdentifier])) {
            throw new NotFoundException('slot', $slotIdentifier);
        }

        return $this->slots[$slotIdentifier];
    }
}
