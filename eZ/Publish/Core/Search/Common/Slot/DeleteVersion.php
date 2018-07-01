<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Search\Common\Slot;

/**
 * A Search Engine slot handling DeleteVersionSignal.
 *
 * @deprecated Slot is deprecated and will be removed as versions are not indexed atm. As of EZP-26186 it does nothing
 */
class DeleteVersion extends Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ContentService\DeleteVersionSignal) {
            return;
        }

        // Do nothing, published version & content is not allowed to be deleted via deleteVersion so we can ignore this.
    }
}
