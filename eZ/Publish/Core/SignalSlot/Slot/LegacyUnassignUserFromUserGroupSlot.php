<?php
/**
 * File containing the LegacyUnassignUserFromUserGroupSlot class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZContentCacheManager;
use eZRole;

/**
 * A legacy slot handling UnAssignUserFromUserGroupSignal.
 */
class LegacyUnassignUserFromUserGroupSlot extends AbstractLegacySlot
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
        if ( !$signal instanceof Signal\UserService\UnAssignUserFromUserGroupSignal )
        {
            return;
        }

        $this->runLegacyKernelCallback(
            function ()
            {
                eZContentCacheManager::clearAllContentCache();
                eZRole::expireCache();
            }
        );
    }
}
