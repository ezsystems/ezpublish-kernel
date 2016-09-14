<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\Slot;

use eZ\Publish\Core\Search\Common\Slot;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A Search Engine slot handling SwapLocationSignal.
 */
class SwapLocation extends Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\LocationService\SwapLocationSignal) {
            return;
        }
        $content1Info = $this->persistenceHandler->contentHandler()->loadContentInfo($signal->content1Id);
        $content2Info = $this->persistenceHandler->contentHandler()->loadContentInfo($signal->content2Id);
        $this->searchHandler->indexContent(
            $this->persistenceHandler->contentHandler()->load($content1Info->id, $content1Info->currentVersionNo)
        );
        $this->searchHandler->indexContent(
            $this->persistenceHandler->contentHandler()->load($content2Info->id, $content2Info->currentVersionNo)
        );
        $this->searchHandler->indexLocation($this->persistenceHandler->locationHandler()->load($signal->location1Id));
        $this->searchHandler->indexLocation($this->persistenceHandler->locationHandler()->load($signal->location2Id));
    }
}
