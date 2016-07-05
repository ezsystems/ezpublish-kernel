<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Common\Slot;

use eZ\Publish\Core\Repository\SignalSlot\Signal;
use eZ\Publish\Core\Search\Common\Slot;

/**
 * A Search Engine slot handling UnhideLocationSignal.
 */
class UnhideLocation extends AbstractSubtree
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\Repository\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\LocationService\UnhideLocationSignal) {
            return;
        }

        $this->indexSubtree($signal->locationId);
    }
}
