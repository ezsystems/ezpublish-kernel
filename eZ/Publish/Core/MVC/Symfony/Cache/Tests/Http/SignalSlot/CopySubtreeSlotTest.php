<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\CopySubtreeSlot;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\CopySubtreeSignal;

class CopySubtreeSlotTest extends AbstractSlotTest
{
    protected static $locationIds = [43];

    /**
     * @dataProvider getReceivedSignals
     */
    public function testReceivePurgesCacheForLocations($signal)
    {
        $this->cachePurgerMock->expects($this->once())
            ->method('purge')
            ->with(self::$locationIds);

        $this->cachePurgerMock->expects($this->never())->method('purgeAll');

        parent::receive($signal);
    }

    public static function createSignal()
    {
        return new CopySubtreeSignal([
            'subtreeId' => 67,
            'targetParentLocationId' => 43,
            'targetNewSubtreeId' => 45,
        ]);
    }

    public function getSlotClass()
    {
        return CopySubtreeSlot::class;
    }

    public static function getReceivedSignalClasses()
    {
        return [CopySubtreeSignal::class];
    }
}
