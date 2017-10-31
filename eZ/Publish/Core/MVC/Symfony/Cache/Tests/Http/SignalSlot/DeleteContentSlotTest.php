<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteContentSignal;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\DeleteContentSlot;

class DeleteContentSlotTest extends AbstractPurgeAllSlotTest implements SlotTest, PurgeAllExpectation
{
    public static function createSignal()
    {
        return new DeleteContentSignal();
    }

    public function getSlotClass()
    {
        return DeleteContentSlot::class;
    }

    public static function getReceivedSignalClasses()
    {
        return [DeleteContentSignal::class];
    }
}
