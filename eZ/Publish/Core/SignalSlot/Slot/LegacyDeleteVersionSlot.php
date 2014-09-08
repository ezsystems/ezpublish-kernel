<?php
/**
 * File containing the Legacy\DeleteVersionSlot class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZContentCacheManager;
use eZContentObject;
use eZSearch;
use eZContentOperationCollection;

/**
 * A legacy slot handling DeleteVersionSignal.
 */
class LegacyDeleteVersionSlot extends AbstractLegacySlot
{
    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return void
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\ContentService\DeleteVersionSignal )
            return;

        $this->runLegacyKernelCallback(
            function () use ( $signal )
            {
                eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId );
                eZSearch::removeObjectById( $signal->contentId, null );
                eZContentOperationCollection::registerSearchObject( $signal->contentId );
                eZContentObject::clearCache();// Clear all object memory cache to free memory
            }
        );
    }
}
