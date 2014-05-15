<?php
/**
 * TrashSignal class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\TrashService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * TrashSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\TrashService
 */
class TrashSignal extends Signal
{
    /**
     * LocationId
     *
     * @var mixed
     */
    public $locationId;
}
