<?php

/**
 * DeleteUserSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\UserService;

use eZ\Publish\Core\SignalSlot\Signal;

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

    /**
     * Affected location id's.
     *
     * List of locations of the content that was deleted, as returned by deleteContent().
     *
     * @var array
     */
    public $affectedLocationIds = [];
}
