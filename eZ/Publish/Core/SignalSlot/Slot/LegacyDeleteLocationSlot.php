<?php
/**
 * File containing the Legacy\DeleteLocationSlot class
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
 * A legacy slot handling DeleteLocationSignal.
 */
class LegacyDeleteLocationSlot extends AbstractLegacySlot
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
        if ( !$signal instanceof Signal\LocationService\DeleteLocationSignal )
            return;

        $this->runLegacyKernelCallback(
            function () use ( $signal )
            {
                // First clear object memory cache to prevent false detection of possibly deleted Content
                eZContentObject::clearCache( $signal->contentId );

                if ( eZContentObject::exists( $signal->contentId ) )
                {
                    // If Content still exists reindex is needed
                    eZContentOperationCollection::registerSearchObject( $signal->contentId );
                }
                else
                {
                    // Else Content was deleted with the last Location, so we remove it from the index
                    eZSearch::removeObjectById( $signal->contentId );
                }

                eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId, true, array( $signal->locationId ) );
                eZSearch::removeNodes( array( $signal->locationId ) );
                eZContentObject::clearCache();// Clear all object memory cache to free memory
            }
        );
    }
}
