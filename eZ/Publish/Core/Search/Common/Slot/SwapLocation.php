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

        $contentInfoList = $this->persistenceHandler->contentHandler()->loadContentInfoList([$signal->content1Id, $signal->content2Id]);
        $this->searchHandler->indexContent(
            $this->persistenceHandler->contentHandler()->load(
                $signal->content1Id,
                $contentInfoList[$signal->content1Id]->currentVersionNo
            )
        );
        $this->searchHandler->indexContent(
            $this->persistenceHandler->contentHandler()->load(
                $signal->content2Id,
                $contentInfoList[$signal->content2Id]->currentVersionNo
            )
        );
        $this->searchHandler->indexLocation($this->persistenceHandler->locationHandler()->load($signal->location1Id));
        $this->searchHandler->indexLocation($this->persistenceHandler->locationHandler()->load($signal->location2Id));
    }
}
