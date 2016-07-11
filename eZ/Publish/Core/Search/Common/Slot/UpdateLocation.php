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
use eZ\Publish\SPI\Search\Indexing\ContentIndexing;
use eZ\Publish\SPI\Search\Indexing\FullTextIndexing;
use eZ\Publish\SPI\Search\Indexing\LocationIndexing;

/**
 * A Search Engine slot handling UpdateLocationSignal.
 */
class UpdateLocation extends Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\LocationService\UpdateLocationSignal || !$this->canIndex()) {
            return;
        }

        if (
            $this->searchHandler instanceof FullTextIndexing &&
            !$this->searchHandler instanceof ContentIndexing &&
            !$this->searchHandler instanceof LocationIndexing
        ) {
            return;
        }

        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo(
            $signal->contentId
        );

        if ($this->canIndexContent()) {
            $this->searchHandler->indexContent(
                $this->persistenceHandler->contentHandler()->load(
                    $signal->contentId,
                    $contentInfo->currentVersionNo
                )
            );
        }

        if ($this->canIndexLocation()) {
            $this->searchHandler->indexLocation(
                $this->persistenceHandler->locationHandler()->load($signal->locationId)
            );
        }
    }
}
