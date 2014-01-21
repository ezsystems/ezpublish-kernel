<?php
/**
 * File containing the LegacyAssignUserToUserGroupSlot class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZContentCacheManager;
use eZRole;

/**
 * A legacy slot handling AssignUserToUserGroupSignal.
 */
class LegacyAssignUserToUserGroupSlot extends AbstractLegacySlot
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
        if ( !$signal instanceof Signal\UserService\AssignUserToUserGroupSignal )
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
