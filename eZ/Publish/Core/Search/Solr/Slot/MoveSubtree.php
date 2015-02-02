<?php
/**
 * File containing the Solr\Slot\MoveSubtree class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Search\Solr\Slot;

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

        $this->indexSubtree( $signal->locationId );
    }

    protected function indexSubtree( $locationId )
    {
        $contentHandler = $this->persistenceHandler->contentHandler();
        $contentSearchHandler = $this->searchHandler->contentSearchHandler();
        $locationHandler = $this->persistenceHandler->locationHandler();
        $locationSearchHandler = $this->searchHandler->locationSearchHandler();

        $processedContentIdSet = array();
        $subtreeIds = $locationHandler->loadSubtreeIds( $locationId );

        foreach ( $subtreeIds as $locationId => $contentId )
        {
            $locationSearchHandler->indexLocation(
                $locationHandler->load( $locationId )
            );

            if ( isset( $processedContentIdSet[$contentId] ) )
            {
                continue;
            }

            $contentSearchHandler->indexContent(
                $contentHandler->load(
                    $contentId,
                    $contentHandler->loadContentInfo( $contentId )->currentVersionNo
                )
            );

            // Content could be found in multiple Locations of the subtree,
            // but we need to (re)index it only once
            $processedContentIdSet[$contentId] = true;
        }
    }
}
