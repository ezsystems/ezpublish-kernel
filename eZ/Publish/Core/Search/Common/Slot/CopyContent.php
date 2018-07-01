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
 * A Search Engine slot handling CopyContentSignal.
 */
class CopyContent extends Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ContentService\CopyContentSignal) {
            return;
        }

        $this->searchHandler->indexContent(
            $this->persistenceHandler->contentHandler()->load(
                $signal->dstContentId,
                $signal->dstVersionNo
            )
        );

        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent(
            $signal->dstContentId
        );
        foreach ($locations as $location) {
            $this->searchHandler->indexLocation($location);
        }
    }
}
