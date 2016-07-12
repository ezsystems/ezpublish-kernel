<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\SignalDispatcher;

use eZ\Publish\API\Repository\Values\ValueObject;

class SignalSlotMap extends ValueObject
{
    /**
     * Signal identifier.
     *
     * @var string
     */
    public $signalIdentifier;

    /**
     * Slot.
     *
     * @var \eZ\Publish\Core\SignalSlot\Slot
     */
    public $slot;
}
