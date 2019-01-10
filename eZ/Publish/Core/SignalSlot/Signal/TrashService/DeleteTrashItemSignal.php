<?php

/**
 * DeleteTrashItemSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\TrashService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * DeleteTrashItemSignal class.
 */
class DeleteTrashItemSignal extends Signal
{
    /**
     * TrashItemId.
     *
     * @var mixed
     */
    public $trashItemId;

    /**
     * Identifier of content associated with deleted TrashItem.
     *
     * @var mixed
     */
    public $contentId;

    /**
     * Indicates that content was removed.
     *
     * @var bool
     */
    public $contentRemoved;
}
