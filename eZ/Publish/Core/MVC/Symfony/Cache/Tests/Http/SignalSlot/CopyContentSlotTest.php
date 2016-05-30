<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\ContentService\CopyContentSignal;

class CopyContentSlotTest extends AbstractContentSlotTest implements SlotTest, PurgeForContentExpectation
{
    protected static $parentLocationId = 59;

    public static function createSignal()
    {
        return new CopyContentSignal(['dstContentId' => static::$contentId, 'dstParentLocationId' => static::$parentLocationId]);
    }

    public static function generateTags()
    {
        return ['content-'.static::$contentId, 'location-'.static::$parentLocationId, 'path-'.static::$parentLocationId];
    }

    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\CopyContentSlot';
    }

    public static function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\ContentService\CopyContentSignal'];
    }
}
