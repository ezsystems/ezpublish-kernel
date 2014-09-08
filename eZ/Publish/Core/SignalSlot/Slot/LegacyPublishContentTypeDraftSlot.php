<?php
/**
 * File containing the Legacy\PublishContentTypeDraft class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
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
     *
     * @return void
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\ContentTypeService\PublishContentTypeDraftSignal )
            return;

        $this->runLegacyKernelCallback(
            function () use ( $signal )
            {
                eZExpiryHandler::registerShutdownFunction();
                $handler = eZExpiryHandler::instance();
                $time = time();
                $handler->setTimestamp( 'user-class-cache', $time );
                $handler->setTimestamp( 'class-identifier-cache', $time );
                $handler->setTimestamp( 'sort-key-cache', $time );
                $handler->store();
            }
        );
    }
}
