<?php

/**
 * SwapLocationSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
     * Parent Location ID of location1.
     *
     * @since 6.7.7
     *
     * @var mixed
     */
    public $parentLocation1Id;

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

    /**
     * Parent Location ID of location2.
     *
     * @since 6.7.7
     *
     * @var mixed
     */
    public $parentLocation2Id;
}
