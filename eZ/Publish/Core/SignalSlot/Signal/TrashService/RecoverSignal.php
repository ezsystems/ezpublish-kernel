<?php

/**
 * RecoverSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\TrashService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * RecoverSignal class.
 */
class RecoverSignal extends Signal
{
    /**
     * TrashItemId.
     *
     * @var mixed
     */
    public $trashItemId;

    /**
     * Content id.
     *
     * @var mixed
     */
    public $contentId;

    /**
     * NewParentLocationId.
     *
     * @var mixed
     */
    public $newParentLocationId;

    /**
     * NewLocationId.
     *
     * @var mixed
     */
    public $newLocationId;
}
