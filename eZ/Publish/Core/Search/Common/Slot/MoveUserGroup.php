<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Common\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Search\Common\Slot;

/**
 * A Search Engine slot handling MoveUserGroupSignal.
 */
class MoveUserGroup extends MoveSubtree
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

        // Moving UserGroup moves its main Location, so we only need to
        // (re)index main Location's subtree
        $this->indexSubtree( $userGroupContentInfo->mainLocationId );
    }
}
