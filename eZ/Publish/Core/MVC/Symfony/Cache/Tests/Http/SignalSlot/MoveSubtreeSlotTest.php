<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\LocationService\MoveSubtreeSignal;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\MoveSubtreeSlot;

class MoveSubtreeSlotTest extends AbstractPurgeAllSlotTest implements SlotTest
{
    public static function createSignal()
    {
        return new MoveSubtreeSignal();
    }

    public function getSlotClass()
    {
        return MoveSubtreeSlot::class;
    }

    public static function getReceivedSignalClasses()
    {
        return [MoveSubtreeSignal::class];
    }
}
