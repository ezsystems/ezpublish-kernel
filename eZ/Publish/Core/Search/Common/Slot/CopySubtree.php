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

/**
 * A Search Engine slot handling CopySubtreeSignal.
 */
class CopySubtree extends Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\LocationService\CopySubtreeSignal || !$this->canIndex()) {
            return;
        }

        $contentHandler = $this->persistenceHandler->contentHandler();
        $subtreeIds = $this->persistenceHandler->locationHandler()->loadSubtreeIds($signal->targetNewSubtreeId);

        foreach ($subtreeIds as $contentId) {
            $contentInfo = $contentHandler->loadContentInfo($contentId);

            if ($this->canIndexContent()) {
                $this->searchHandler->indexContent(
                    $contentHandler->load($contentInfo->id, $contentInfo->currentVersionNo)
                );
            }

            if ($this->canIndexLocation()) {
                $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($contentInfo->id);
                foreach ($locations as $location) {
                    $this->searchHandler->indexLocation($location);
                }
            }
        }
    }

    protected function canIndexContent()
    {
        return $this->searchHandler instanceof ContentIndexing || $this->searchHandler instanceof FullTextIndexing;
    }
}
