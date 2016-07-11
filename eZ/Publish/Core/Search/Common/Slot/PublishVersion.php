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
 * A Search Engine slot handling PublishVersionSignal.
 */
class PublishVersion extends Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ContentService\PublishVersionSignal || !$this->canIndex()) {
            return;
        }

        if ($this->canIndexContent()) {
            $this->searchHandler->indexContent(
                $this->persistenceHandler->contentHandler()->load($signal->contentId, $signal->versionNo)
            );
        }

        if ($this->canIndexLocation()) {
            $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($signal->contentId);
            foreach ($locations as $location) {
                $this->searchHandler->indexLocation($location);
            }
        }
    }

    protected function canIndexContent()
    {
        return $this->searchHandler instanceof ContentIndexing || $this->searchHandler instanceof FullTextIndexing;
    }
}
