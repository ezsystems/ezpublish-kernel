<?php
/**
 * File containing the Legacy\CreateLocationSlot class
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

        $this->runLegacyKernelCallback(
            function () use ( $signal )
            {
                eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId, true, array( $signal->locationId ) );
                $object = eZContentObject::fetch( $signal->contentId );
                eZSearch::addNodeAssignment( $object->mainNodeID(), $signal->contentId, $signal->locationId );
                eZContentObject::clearCache();// Clear all object memory cache to free memory
            }
        );
    }
}
