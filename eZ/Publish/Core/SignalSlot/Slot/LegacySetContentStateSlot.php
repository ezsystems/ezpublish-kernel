<?php
/**
 * File containing the Legacy\SetContentStateSlot class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot\AbstractLegacySlot;

/**
 * A legacy slot handling SetContentStateSignal.
 */
class LegacySetContentStateSlot extends AbstractLegacySlot
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
        if ( !$signal instanceof Signal\ObjectStateService\SetContentStateSignal )
            return;

        $kernel = $this->getLegacyKernel();
        $kernel->runCallback( function() use( $signal )
            {
                \eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId );
                \eZSearch::updateObjectState( $signal->contentId, array( $signal->objectStateId ) );
            },
            false
        );
    }
}
