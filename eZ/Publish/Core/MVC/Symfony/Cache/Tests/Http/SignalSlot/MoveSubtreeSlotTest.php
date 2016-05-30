<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\LocationService\MoveSubtreeSignal;

class MoveSubtreeSlotTest extends AbstractContentSlotTest implements SlotTest, PurgeForContentExpectation
{
    protected static $locationId = 45;
    protected static $parentLocationId = 43;

    public static function createSignal()
    {
        return new MoveSubtreeSignal(
            [
                'locationId' => static::$locationId,
                'newParentLocationId' => static::$parentLocationId
            ]
        );
    }

    public static function generateTags()
    {
        return ['path-'.static::$locationId, 'location-'.static::$parentLocationId, 'parent-'.static::$parentLocationId];
    }

    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\MoveSubtreeSlot';
    }

    public static function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\LocationService\MoveSubtreeSignal'];
    }
}
