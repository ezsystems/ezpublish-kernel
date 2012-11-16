<?php
/**
 * File containing the Legacy\PublishContentTypeDraft class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot\AbstractLegacySlot;
use eZExpiryHandler;

/**
 * A legacy slot handling PublishContentTypeDraftSignal.
 */
class LegacyPublishContentTypeDraftSlot extends AbstractLegacySlot
{
    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     * @return void
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\ContentTypeService\PublishContentTypeDraftSignal )
            return;

        $kernel = $this->getLegacyKernel();
        $kernel->runCallback(
            function () use( $signal )
            {
                eZExpiryHandler::registerShutdownFunction();
                $handler = eZExpiryHandler::instance();
                $time = time();
                $handler->setTimestamp( 'user-class-cache', $time );
                $handler->setTimestamp( 'class-identifier-cache', $time );
                $handler->setTimestamp( 'sort-key-cache', $time );
                $handler->store();
            },
            false
        );
    }
}
