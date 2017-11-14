<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\TrashService\RecoverSignal;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\RecoverSlot;

class RecoverSlotTest extends AbstractPurgeForContentSlotTest implements SlotTest, PurgeForContentExpectation
{
    protected static $locationIds = [43];

    public static function createSignal()
    {
        return new RecoverSignal(['contentId' => self::getContentId(), 'newParentLocationId' => 43]);
    }

    public function getSlotClass()
    {
        return RecoverSlot::class;
    }

    public static function getReceivedSignalClasses()
    {
        return [RecoverSignal::class];
    }
}
