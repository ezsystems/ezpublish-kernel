<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\UpdateLocationSlot;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\UpdateLocationSignal;

class UpdateLocationSlotTest extends AbstractPurgeForContentSlotTest implements SlotTest, PurgeForContentExpectation
{
    public static function createSignal()
    {
        return new UpdateLocationSignal(['contentId' => self::getContentId()]);
    }

    public function getSlotClass()
    {
        return UpdateLocationSlot::class;
    }

    public static function getReceivedSignalClasses()
    {
        return [UpdateLocationSignal::class];
    }
}
