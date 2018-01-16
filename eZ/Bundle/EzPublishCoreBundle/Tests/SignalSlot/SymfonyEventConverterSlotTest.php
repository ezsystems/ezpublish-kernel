<?php

/**
 * File containing the SymfonyEventConverterSlotTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\SignalSlot;

use eZ\Bundle\EzPublishCoreBundle\SignalSlot\Slot\SymfonyEventConverterSlot;
use eZ\Publish\Core\MVC\Symfony\Event\SignalEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\SignalSlot\Signal;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SymfonyEventConverterSlotTest extends TestCase
{
    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\SignalSlot\Slot\SymfonyEventConverterSlot::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\SignalSlot\Slot\SymfonyEventConverterSlot::receive
     */
    public function testReceive()
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(MVCEvents::API_SIGNAL, $this->isInstanceOf(SignalEvent::class));

        $slot = new SymfonyEventConverterSlot($eventDispatcher);
        $slot->receive($this->createMock(Signal::class));
    }
}
