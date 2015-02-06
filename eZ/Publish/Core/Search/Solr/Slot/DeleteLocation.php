<?php
/**
 * File containing the Solr\Slot\DeleteLocation class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Search\Solr\Slot;

/**
 * A Solr slot handling DeleteLocationSignal.
 */
class DeleteLocation extends Slot
{
    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\LocationService\DeleteLocationSignal )
            return;

        $this->searchHandler->contentSearchHandler()->deleteLocation( $signal->locationId, $signal->contentId );
        $this->searchHandler->locationSearchHandler()->deleteLocation( $signal->locationId );
    }
}
