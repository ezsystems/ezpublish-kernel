<?php
/**
 * File containing the Legacy\CreateLocationSlot class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot\AbstractLegacySlot;
use eZContentCacheManager;
use eZContentObject;
use eZSearch;

/**
 * A legacy slot handling CreateLocationSignal.
 */
class LegacyCreateLocationSlot extends AbstractLegacySlot
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
        if ( !$signal instanceof Signal\LocationService\CreateLocationSignal )
            return;

        $kernel = $this->getLegacyKernel();
        $kernel->runCallback(
            function () use ( $signal )
            {
                eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId, true, array( $signal->locationId ) );
                $object = eZContentObject::fetch( $signal->contentId );
                eZSearch::addNodeAssignment( $object->mainNodeID(), $signal->contentId, $signal->locationId );
                eZContentObject::clearCache();// Clear all object memory cache to free memory
            },
            false
        );
    }
}
