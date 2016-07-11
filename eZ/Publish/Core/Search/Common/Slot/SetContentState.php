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

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Search\Common\Slot;
use eZ\Publish\SPI\Search\Indexing;
use eZ\Publish\SPI\Search\Indexing\ContentIndexing;
use eZ\Publish\SPI\Search\Indexing\FullTextIndexing;
use eZ\Publish\SPI\Search\Indexing\LocationIndexing;

/**
 * A Search Engine slot handling SetContentStateSignal.
 */
class SetContentState extends Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ObjectStateService\SetContentStateSignal) {
            return;
        }

        if (!$this->searchHandler instanceof Indexing || $this->searchHandler instanceof FullTextIndexing) {
            return;
        }

        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo($signal->contentId);

        if ($this->searchHandler instanceof ContentIndexing) {
            $this->searchHandler->indexContent(
                $this->persistenceHandler->contentHandler()->load(
                    $contentInfo->id,
                    $contentInfo->currentVersionNo
                )
            );
        }

        if ($this->searchHandler instanceof LocationIndexing) {
            $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($contentInfo->id);
            foreach ($locations as $location) {
                $this->searchHandler->indexLocation($location);
            }
        }
    }
}
