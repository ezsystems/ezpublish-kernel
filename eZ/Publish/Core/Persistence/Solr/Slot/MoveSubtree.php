<?php
/**
 * File containing the Solr\Slot\MoveSubtree class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Persistence\Solr\Slot;

/**
 * A Solr slot handling MoveSubtreeSignal.
 */
class MoveSubtree extends Slot
{
    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
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
            $this->persistenceHandler->searchHandler()->indexContent(
                $contentHandler->load( $contentInfo->id, $contentInfo->currentVersionNo )
            );

            $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent( $contentInfo->id );
            foreach ( $locations as $location )
            {
                $this->persistenceHandler->locationSearchHandler()->indexLocation( $location );
            }
        }
    }
}
