<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\Slot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A Search Engine slot handling AssignUserToUserGroupSignal.
 */
class AssignUserToUserGroup extends AbstractSubtree
{
    public function receive(Signal $signal): void
    {
        if (!$signal instanceof Signal\UserService\AssignUserToUserGroupSignal) {
            return;
        }

        $this->indexSubtree($signal->affectedLocationId);
    }
}
