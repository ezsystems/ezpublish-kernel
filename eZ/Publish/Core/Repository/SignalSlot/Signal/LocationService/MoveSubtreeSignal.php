<?php

/**
 * MoveSubtreeSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Signal\LocationService;

use eZ\Publish\Core\Repository\SignalSlot\Signal;

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
     * NewParentLocationId.
     *
     * @var mixed
     */
    public $newParentLocationId;
}
