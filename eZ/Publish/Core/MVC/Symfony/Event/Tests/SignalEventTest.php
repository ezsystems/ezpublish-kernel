<?php

/**
 * File containing the SignalEventTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Event\Tests;

use eZ\Publish\Core\MVC\Symfony\Event\SignalEvent;
use eZ\Publish\Core\SignalSlot\Signal;
use PHPUnit\Framework\TestCase;

class SignalEventTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Event\SignalEvent::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Event\SignalEvent::getSignal
     */
    public function testGetSignal()
    {
        $signal = $this->createMock(Signal::class);
        $event = new SignalEvent($signal);
        $this->assertSame($signal, $event->getSignal());
    }
}
