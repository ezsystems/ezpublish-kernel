<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteVersionSignal;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\DeleteVersionSlot;

class DeleteVersionSlotTest extends AbstractPurgeForContentSlotTest implements SlotTest, PurgeForContentExpectation
{
    public static function createSignal()
    {
        return new DeleteVersionSignal(['contentId' => self::getContentId()]);
    }

    public function getSlotClass()
    {
        return DeleteVersionSlot::class;
    }

    public static function getReceivedSignalClasses()
    {
        return [DeleteVersionSignal::class];
    }
}
