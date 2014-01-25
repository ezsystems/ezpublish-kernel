<?php
/**
 * File containing the SymfonyEventConverterSlotTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\SignalSlot;

use eZ\Bundle\EzPublishCoreBundle\SignalSlot\Slot\SymfonyEventConverterSlot;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use PHPUnit_Framework_TestCase;

class SymfonyEventConverterSlotTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\SignalSlot\Slot\SymfonyEventConverterSlot::__construct
     * @covers eZ\Bundle\EzPublishCoreBundle\SignalSlot\Slot\SymfonyEventConverterSlot::receive
     */
    public function testReceive()
    {
        $eventDispatcher = $this->getMock( 'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface' );
        $eventDispatcher
            ->expects( $this->once() )
            ->method( 'dispatch' )
            ->with( MVCEvents::API_SIGNAL, $this->isInstanceOf( 'eZ\\Publish\\Core\\MVC\\Symfony\\Event\\SignalEvent' ) );

        $slot = new SymfonyEventConverterSlot( $eventDispatcher );
        $slot->receive( $this->getMock( 'eZ\\Publish\\Core\\Repository\\SignalSlot\\Signal' ) );
    }
}
