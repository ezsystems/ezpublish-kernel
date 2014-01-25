<?php
/**
 * File containing the Legacy\DeleteLocationSlot class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\SignalSlot\Slot;

use eZ\Publish\Core\Repository\SignalSlot\Signal;
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
     * @param \eZ\Publish\Core\Repository\SignalSlot\Signal $signal
     *
     * @return void
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\LocationService\DeleteLocationSignal )
            return;

        $kernel = $this->getLegacyKernel();
        $kernel->runCallback(
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
            },
            false
        );
    }
}
