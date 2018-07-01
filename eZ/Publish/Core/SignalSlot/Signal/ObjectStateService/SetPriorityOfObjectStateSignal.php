<?php

/**
 * SetPriorityOfObjectStateSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\ObjectStateService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * SetPriorityOfObjectStateSignal class.
 */
class SetPriorityOfObjectStateSignal extends Signal
{
    /**
     * ObjectStateId.
     *
     * @var mixed
     */
    public $objectStateId;

    /**
     * Priority.
     *
     * @var mixed
     */
    public $priority;
}
