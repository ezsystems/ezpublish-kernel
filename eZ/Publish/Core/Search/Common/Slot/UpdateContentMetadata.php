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

class UpdateContentMetadata extends Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ContentService\UpdateContentMetadataSignal) {
            return;
        }

        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo($signal->contentId);
        if (!$contentInfo->isPublished) {
            return;
        }
        $this->searchHandler->indexContent(
            $this->persistenceHandler->contentHandler()->load($contentInfo->id, $contentInfo->currentVersionNo)
        );
        $this->searchHandler->indexLocation($this->persistenceHandler->locationHandler()->load($contentInfo->mainLocationId));
    }
}
