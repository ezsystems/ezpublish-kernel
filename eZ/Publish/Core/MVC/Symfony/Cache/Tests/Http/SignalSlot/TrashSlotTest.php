<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\TrashService\TrashSignal;

class TrashSlotTest extends AbstractContentSlotTest implements SlotTest, PurgeForContentExpectation
{
    protected static $locationId = 45;
    protected static $parentLocationId = 43;

    public static function createSignal()
    {
        return new TrashSignal(
            [
                'contentId' => static::$contentId,
                'locationId' => static::$locationId,
                'parentLocationId' => static::$parentLocationId
            ]
        );
    }

    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\TrashSlot';
    }

    public static function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\TrashService\TrashSignal'];
    }
}
