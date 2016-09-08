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
class SwapLocationSlotTest extends AbstractContentSlotTest
{
    public function setUp()
    {
        $this->markTestIncomplete('fixme');
    }

    public function createSignal()
    {
        return new SwapLocationSignal(['content1Id' => $this->contentId]);
    }

    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\SetContentStateSlot';
    }

    public function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\SetContentStateSignal'];
    }
}
