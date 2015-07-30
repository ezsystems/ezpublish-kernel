<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling UnAssignUserFromUserGroupSignal.
 *
 * @todo
 * Is this right ? Does it require a full wipe of the cache ? Very unlikely.
 * The User's Content's HTTP cache must be cleared, yes.
 * And the user must be logged out, or its user hash cleared (not sure we can without clearing for all users)
 */
class UnassignUserFromUserGroupSlot extends PurgeAllHttpCacheSlot
{
    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\UserService\UnAssignUserFromUserGroupSignal;
    }
}
