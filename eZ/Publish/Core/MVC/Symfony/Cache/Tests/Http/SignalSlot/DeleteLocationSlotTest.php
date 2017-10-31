<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\LocationService\DeleteLocationSignal;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\DeleteLocationSlot;

class DeleteLocationSlotTest extends AbstractPurgeForContentSlotTest implements SlotTest, PurgeForContentExpectation
{
    protected static $locationIds = [45, 43];

    public static function createSignal()
    {
        return new DeleteLocationSignal(['contentId' => self::getContentId(), 'locationId' => 45, 'parentLocationId' => 43]);
    }

    public function getSlotClass()
    {
        return DeleteLocationSlot::class;
    }

    public static function getReceivedSignalClasses()
    {
        return [DeleteLocationSignal::class];
    }
}
