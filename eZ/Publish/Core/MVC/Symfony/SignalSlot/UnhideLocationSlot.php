<?php
/**
 * File containing the Legacy\UnhideLocationSlot class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZContentObject;
use eZContentObjectTreeNode;
use eZSearch;

/**
 * A slot handling UnhideLocationSignal.
 */
class UnhideLocationSlot extends AbstractSlot
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

        $this->runLegacyKernelCallback(
            function () use ( $signal )
            {
                $node = eZContentObjectTreeNode::fetch( $signal->locationId );
                eZContentObjectTreeNode::clearViewCacheForSubtree( $node );
                eZSearch::updateNodeVisibility( $signal->locationId, 'show' );
                eZContentObject::clearCache();// Clear all object memory cache to free memory
            }
        );
    }
}
