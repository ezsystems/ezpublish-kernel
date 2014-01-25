<?php
/**
 * File containing the SymfonyEventConverterSlot class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\SignalSlot\Slot;

use eZ\Publish\Core\MVC\Symfony\Event\SignalEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\Repository\SignalSlot\Signal;
use eZ\Publish\Core\Repository\SignalSlot\Slot;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Generic slot that converts signals emitted by Repository services into Symfony events.
 */
class SymfonyEventConverterSlot extends Slot
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct( EventDispatcherInterface $eventDispatcher )
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Receive the given $signal and react on it
     *
     * @param Signal $signal
     *
     * @return void
     */
    public function receive( Signal $signal )
    {
        $this->eventDispatcher->dispatch( MVCEvents::API_SIGNAL, new SignalEvent( $signal ) );
    }
}
