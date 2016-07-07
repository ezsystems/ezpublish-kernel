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
use eZ\Publish\SPI\Search\Indexer;
use eZ\Publish\SPI\Search\Indexer\ContentIndexer;
use eZ\Publish\SPI\Search\Indexer\LocationIndexer;

/**
 * A Search Engine slot handling CreateLocationSignal.
 */
class CreateLocation extends Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\LocationService\CreateLocationSignal) {
            return;
        }

        if (!$this->searchHandler instanceof Indexer) {
            return;
        }

        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo(
            $signal->contentId
        );

        if ($this->searchHandler instanceof ContentIndexer) {
            $this->searchHandler->indexContent(
                $this->persistenceHandler->contentHandler()->load(
                    $signal->contentId,
                    $contentInfo->currentVersionNo
                )
            );
        }

        if ($this->searchHandler instanceof LocationIndexer) {
            $this->searchHandler->indexLocation(
                $this->persistenceHandler->locationHandler()->load($signal->locationId)
            );
        }
    }
}
