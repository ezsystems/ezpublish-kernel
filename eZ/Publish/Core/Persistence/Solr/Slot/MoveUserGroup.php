<?php
/**
 * File containing the Solr\Slot\MoveUserGroup class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Persistence\Solr\Slot;

/**
 * A Solr slot handling MoveUserGroupSignal.
 */
class MoveUserGroup extends Slot
{
    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\UserService\MoveUserGroupSignal )
            return;

        $userGroupContentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo( $signal->userGroupId );

        $this->enqueueIndexing(
            $this->persistenceHandler->contentHandler()->load(
                $userGroupContentInfo->id,
                $userGroupContentInfo->currentVersionNo
            )
        );
    }
}
