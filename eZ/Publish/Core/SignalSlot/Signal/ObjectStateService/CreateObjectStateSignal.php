<?php

/**
 * CreateObjectStateSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\ObjectStateService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateObjectStateSignal class.
 */
class CreateObjectStateSignal extends Signal
{
    /**
     * ObjectStateGroupId.
     *
     * @var mixed
     */
    public $objectStateGroupId;

    /**
     * ObjectStateId.
     *
     * @var mixed
     */
    public $objectStateId;
}
