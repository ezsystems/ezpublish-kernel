<?php
/**
 * File containing the Solr\Slot\Trash class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Persistence\Solr\Slot;

/**
 * A Solr slot handling TrashSignal.
 */
class Trash extends Slot
{
    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\TrashService\TrashSignal )
            return;

        $this->persistenceHandler->locationSearchHandler()->deleteLocation( $signal->locationId );
        $this->persistenceHandler->searchHandler()->deleteLocation( $signal->locationId );
    }
}
