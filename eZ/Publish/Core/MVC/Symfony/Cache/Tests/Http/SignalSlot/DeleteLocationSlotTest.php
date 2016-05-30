<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\LocationService\DeleteLocationSignal;

class DeleteLocationSlotTest extends AbstractContentSlotTest implements SlotTest, PurgeForContentExpectation
{
    protected static $locationId = 45;
    protected static $parentLocationId = 43;

    public static function createSignal()
    {
        return new DeleteLocationSignal(
            [
                'contentId' => static::$contentId,
                'locationId' => static::$locationId,
                'parentLocationId' => static::$parentLocationId
            ]
        );
    }

    public static function generateTags()
    {
        $tags = parent::generateTags();
        $tags[] = 'path-'.static::$locationId;

        return $tags;
    }

    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\DeleteLocationSlot';
    }

    public static function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\LocationService\DeleteLocationSignal'];
    }
}
