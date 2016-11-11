<?php

/**
 * UpdateUserSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\UserService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateUserSignal class.
 */
class UpdateUserSignal extends Signal
{
    /**
     * UserId.
     *
     * @var mixed
     */
    public $userId;
}
