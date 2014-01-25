<?php
/**
 * File containing the Solr\Slot\MoveSubtree class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Slot;

use eZ\Publish\Core\Repository\SignalSlot\Signal;
use eZ\Publish\Core\Persistence\Solr\Slot;

/**
 * A Solr slot handling MoveSubtreeSignal.
 */
class MoveSubtree extends Slot
{
    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\Repository\SignalSlot\Signal $signal
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\LocationService\MoveSubtreeSignal )
            return;

        $contentHandler = $this->persistenceHandler->contentHandler();

        foreach (
            $this->persistenceHandler->locationHandler()->loadSubtreeIds( $signal->locationId ) as $contentId
        )
        {
            $contentInfo = $contentHandler->loadContentInfo( $contentId );
            $this->enqueueIndexing( $contentHandler->load( $contentInfo->id, $contentInfo->currentVersionNo ) );
        }
    }
}
