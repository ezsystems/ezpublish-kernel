<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests\SignalDispatcher;

use eZ\Publish\Core\SignalSlot;
use PHPUnit\Framework\TestCase;

/**
 * @group signalSlot
 */
class DefaultSignalDispatcherTest extends TestCase
{
    public function testEmitSignalNoSlot()
    {
        $signal = $this->createMock(SignalSlot\Signal::class);

        $dispatcher = new SignalSlot\SignalDispatcher\DefaultSignalDispatcher();
        $dispatcher->emit($signal);
    }

    public function testEmitSignalSingleSlot()
    {
        $signal = $this->createMock(SignalSlot\Signal::class);
        $slot = $this->createMock(SignalSlot\Slot::class);
        $slot
            ->expects($this->once())
            ->method('receive')
            ->with($signal);

        $dispatcher = new SignalSlot\SignalDispatcher\DefaultSignalDispatcher();
        $dispatcher->attach('\\' . get_class($signal), $slot);
        $dispatcher->emit($signal);
    }

    public function testEmitSignalSingleSlotRelative()
    {
        $signal = new SignalSlot\Signal\ContentService\PublishVersionSignal();
        $slot = $this->createMock(SignalSlot\Slot::class);
        $slot
            ->expects($this->once())
            ->method('receive')
            ->with($signal);

        $dispatcher = new SignalSlot\SignalDispatcher\DefaultSignalDispatcher();
        $dispatcher->attach('ContentService\\PublishVersionSignal', $slot);
        $dispatcher->emit($signal);
    }

    public function testEmitSignalMultipleSlots()
    {
        $signal = $this->createMock(SignalSlot\Signal::class);
        $slot = $this->createMock(SignalSlot\Slot::class);
        $slot
            ->expects($this->once())
            ->method('receive')
            ->with($signal);
        $slot2 = $this->createMock(SignalSlot\Slot::class);
        $slot2
            ->expects($this->once())
            ->method('receive')
            ->with($signal);
        $slot3 = $this->createMock(SignalSlot\Slot::class);
        $slot3
            ->expects($this->once())
            ->method('receive')
            ->with($signal);

        $dispatcher = new SignalSlot\SignalDispatcher\DefaultSignalDispatcher();
        $dispatcher->attach('\\' . get_class($signal), $slot);
        $dispatcher->attach('\\' . get_class($signal), $slot2);
        // Registering a wildcard slot. It is supposed to receive all the signals, whatever they are.
        $dispatcher->attach('*', $slot3);
        $dispatcher->emit($signal);
    }
}
