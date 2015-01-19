<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling AssignUserToUserGroupSignal.
 */
class AssignUserToUserGroupSlot extends AbstractSlot
{
    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal\UserService\AssignUserToUserGroupSignal $signal
     */
    protected function extractContentId( Signal $signal )
    {
        return $signal->userId;
    }

    protected function supports( Signal $signal )
    {
        return $signal instanceof Signal\UserService\AssignUserToUserGroupSignal;
    }
}
