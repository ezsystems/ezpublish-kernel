<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\LocationService\SwapLocationSignal;

/**
 * @todo Fixme
 */
class SwapLocationSlotTest extends AbstractPurgeForContentSlotTest implements SlotTest, PurgeForContentExpectation
{
    public function setUp()
    {
        self::markTestIncomplete('fixme');
    }

    public static function createSignal()
    {
        return new SwapLocationSignal(['content1Id' => self::getContentId()]);
    }

    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\SetContentStateSlot';
    }

    public static function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\SetContentStateSignal'];
    }
}
