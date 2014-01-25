<?php
/**
 * File containing the LegacyUnassignUserFromUserGroupSlot class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\SignalSlot\Slot;

use eZ\Publish\Core\Repository\SignalSlot\Signal;
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
     * @param \eZ\Publish\Core\Repository\SignalSlot\Signal $signal
     *
     * @return void
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\UserService\UnAssignUserFromUserGroupSignal )
        {
            return;
        }

        $kernel = $this->getLegacyKernel();
        $kernel->runCallback(
            function ()
            {
                eZContentCacheManager::clearAllContentCache();
                eZRole::expireCache();
            },
            false
        );
    }
}
