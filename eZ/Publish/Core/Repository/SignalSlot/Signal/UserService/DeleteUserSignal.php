<?php

/**
 * DeleteUserSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Signal\UserService;

use eZ\Publish\Core\Repository\SignalSlot\Signal;

/**
 * DeleteUserSignal class.
 */
class DeleteUserSignal extends Signal
{
    /**
     * UserId.
     *
     * @var mixed
     */
    public $userId;
}
