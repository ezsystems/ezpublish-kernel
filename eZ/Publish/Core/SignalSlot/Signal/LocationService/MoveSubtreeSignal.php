<?php

/**
 * MoveSubtreeSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\LocationService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * MoveSubtreeSignal class.
 */
class MoveSubtreeSignal extends Signal
{
    /**
     * LocationId.
     *
     * @var mixed
     */
    public $locationId;

    /**
     * Location ID of original parent location of the moved location.
     *
     * @since 6.7.7
     *
     * @var mixed
     */
    public $oldParentLocationId;

    /**
     * NewParentLocationId.
     *
     * @var mixed
     */
    public $newParentLocationId;
}
