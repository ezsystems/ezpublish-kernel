<?php

/**
 * TrashSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\TrashService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * TrashSignal class.
 */
class TrashSignal extends Signal
{
    /**
     * LocationId.
     *
     * @var mixed
     */
    public $locationId;

    /**
     * Location ID of parent location of the trashed location.
     *
     * @var mixed
     */
    public $parentLocationId;

    /**
     * Content id.
     *
     * @var mixed
     */
    public $contentId;

    /**
     * If content was trashed or if just location was deleted.
     *
     * @var bool
     */
    public $contentTrashed;
}
