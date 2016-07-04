<?php

/**
 * CreateObjectStateSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Signal\ObjectStateService;

use eZ\Publish\Core\Repository\SignalSlot\Signal;

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
