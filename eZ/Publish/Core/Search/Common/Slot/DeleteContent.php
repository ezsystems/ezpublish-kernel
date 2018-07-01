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
 * A Search Engine slot handling DeleteContentSignal.
 */
class DeleteContent extends Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ContentService\DeleteContentSignal) {
            return;
        }

        // Delete Content
        $this->searchHandler->deleteContent($signal->contentId);

        // Delete locations if there is any
        foreach ($signal->affectedLocationIds as $locationId) {
            $this->searchHandler->deleteLocation($locationId, $signal->contentId);
        }
    }
}
