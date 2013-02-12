<?php
/**
 * File containing the Legacy\UnhideLocationSlot class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot\AbstractLegacySlot;

/**
 * A legacy slot handling UnhideLocationSignal.
 */
class LegacyUnhideLocationSlot extends AbstractLegacySlot
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
        if ( !$signal instanceof Signal\LocationService\UnhideLocationSignal )
            return;

        $kernel = $this->getLegacyKernel();
        $kernel->runCallback(
            function () use ( $signal )
            {
                $node = \eZContentObjectTreeNode::fetch( $signal->locationId );
                \eZContentObjectTreeNode::clearViewCacheForSubtree( $node );
                \eZSearch::updateNodeVisibility( $signal->locationId, 'show' );
            },
            false
        );
    }
}
