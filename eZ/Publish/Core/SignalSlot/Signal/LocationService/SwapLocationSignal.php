<?php

/**
 * SwapLocationSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Signal\LocationService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * SwapLocationSignal class.
 */
class SwapLocationSignal extends Signal
{
    /**
     * Content1 Id.
     *
     * @var mixed
     */
    public $content1Id;

    /**
     * Location1 Id.
     *
     * @var mixed
     */
    public $location1Id;

    /**
     * Content2 Id.
     *
     * @var mixed
     */
    public $content2Id;

    /**
     * Location2 Id.
     *
     * @var mixed
     */
    public $location2Id;
}
