<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Search\Common\Slot;
use eZ\Publish\SPI\Search\ContentTranslationHandler;

/**
 * A Search Engine slot handling DeleteTranslationSignal.
 */
class DeleteTranslation extends Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ContentService\DeleteTranslationSignal) {
            return;
        }

        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo(
            $signal->contentId
        );
        if (!$contentInfo->isPublished) {
            return;
        }

        if ($this->searchHandler instanceof ContentTranslationHandler) {
            $this->searchHandler->deleteTranslation(
                $contentInfo->id,
                $signal->languageCode
            );
        }

        $this->searchHandler->indexContent(
            $this->persistenceHandler->contentHandler()->load(
                $contentInfo->id,
                $contentInfo->currentVersionNo
            )
        );

        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent(
            $contentInfo->id
        );
        foreach ($locations as $location) {
            $this->searchHandler->indexLocation($location);
        }
    }
}
