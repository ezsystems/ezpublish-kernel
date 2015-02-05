<?php
/**
 * File containing the Solr\Slot\CreateLocation class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Persistence\Solr\Slot;

/**
 * A Solr slot handling CreateLocationSignal.
 */
class CreateLocation extends Slot
{
    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\LocationService\CreateLocationSignal )
        {
            return;
        }

        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo( $signal->contentId );
        $this->searchHandler->contentSearchHandler()->indexContent(
            $this->persistenceHandler->contentHandler()->load( $signal->contentId, $contentInfo->currentVersionNo )
        );

        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent( $signal->contentId );
        foreach ( $locations as $location )
        {
            $this->searchHandler->locationSearchHandler()->indexLocation( $location );
        }
    }
}
