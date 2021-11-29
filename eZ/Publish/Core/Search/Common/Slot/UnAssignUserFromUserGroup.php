<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Search\Common\Slot;

/**
 * A Search Engine slot handling UnAssignUserFromUserGroup.
 */
class UnAssignUserFromUserGroup extends Slot
{
    public function receive(Signal $signal): void
    {
        if (!$signal instanceof Signal\UserService\UnAssignUserFromUserGroupSignal) {
            return;
        }

        $content = $this->persistenceHandler->contentHandler()->load($signal->userId);
        $this->searchHandler->indexContent($content);
    }
}
