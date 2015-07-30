<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal;

class PublishVersionSlotTest extends AbstractPurgeForContentSlotTest implements SlotTest, PurgeForContentExpectation
{
    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\PublishVersionSlot';
    }

    public static function createSignal()
    {
        return new PublishVersionSignal(['contentId' => self::getContentId()]);
    }

    public static function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal'];
    }
}
