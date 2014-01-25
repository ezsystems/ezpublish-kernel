<?php
/**
 * File containing the Legacy\MoveSubtreeSlot class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\SignalSlot\Slot;

use eZ\Publish\Core\Repository\SignalSlot\Signal;
use eZContentObject;
use eZContentObjectTreeNode;
use eZContentOperationCollection;

/**
 * A legacy slot handling MoveSubtreeSignal.
 */
class LegacyMoveSubtreeSlot extends AbstractLegacySlot
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
        if ( !$signal instanceof Signal\LocationService\MoveSubtreeSignal )
            return;

        $kernel = $this->getLegacyKernel();
        $kernel->runCallback(
            function () use ( $signal )
            {
                $node = eZContentObjectTreeNode::fetch( $signal->locationId );
                eZContentObjectTreeNode::clearViewCacheForSubtree( $node );
                eZContentOperationCollection::registerSearchObject( $node->attribute( 'contentobject_id' ) );
                eZContentObject::clearCache();// Clear all object memory cache to free memory
            },
            false
        );
    }
}
