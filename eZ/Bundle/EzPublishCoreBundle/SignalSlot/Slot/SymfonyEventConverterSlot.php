<?php

/**
 * File containing the SymfonyEventConverterSlot class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\SignalSlot\Slot;

use eZ\Publish\Core\MVC\Symfony\Event\SignalEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Generic slot that converts signals emitted by Repository services into Symfony events.
 */
class SymfonyEventConverterSlot extends Slot
{
    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Receive the given $signal and react on it.
     *
     * @param Signal $signal
     */
    public function receive(Signal $signal)
    {
        $this->eventDispatcher->dispatch(MVCEvents::API_SIGNAL, new SignalEvent($signal));
    }
}
