<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Tests\SignalDispatcher;

use eZ\Publish\Core\SignalSlot;

/**
 * @group signalSlot
 * @covers \eZ\Publish\Core\SignalSlot\SignalDispatcher\DefaultSignalDispatcher
 */
class DefaultSignalDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testEmitSignalNoSlot()
    {
        $factory = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\SlotFactory' );
        $factory
            ->expects( $this->never() )
            ->method( 'getSlot' );

        $signal = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\Signal' );

        $dispatcher = new SignalSlot\SignalDispatcher\DefaultSignalDispatcher( $factory );
        $dispatcher->emit( $signal );
    }

    public function testGetSlotSingleSlot()
    {
        $signal = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\Signal' );

        $slot = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\Slot' );

        $factory = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\SlotFactory' );
        $factory
            ->expects( $this->once() )
            ->method( 'getSlot' )
            ->with( 'my_slot' )
            ->will( $this->returnValue( $slot ) );

        $dispatcher = new SignalSlot\SignalDispatcher\DefaultSignalDispatcher( $factory );
        $dispatcher->attach( get_class( $signal ), 'my_slot' );
        $dispatcher->emit( $signal );
    }

    public function testGetSlotMultipleSlots()
    {
        $signal = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\Signal' );

        $slot = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\Slot' );

        $factory = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\SlotFactory' );
        $factory
            ->expects( $this->at( 0 ) )
            ->method( 'getSlot' )
            ->with( 'my_slot' )
            ->will( $this->returnValue( $slot ) );
        $factory
            ->expects( $this->at( 1 ) )
            ->method( 'getSlot' )
            ->with( 'my_second_slot' )
            ->will( $this->returnValue( $slot ) );

        $dispatcher = new SignalSlot\SignalDispatcher\DefaultSignalDispatcher( $factory );
        $dispatcher->attach( get_class( $signal ), 'my_slot' );
        $dispatcher->attach( get_class( $signal ), 'my_second_slot' );
        $dispatcher->emit( $signal );
    }

    public function testEmitSignalSingleSlot()
    {
        $signal = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\Signal' );

        $slot = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\Slot' );
        $slot
            ->expects( $this->once() )
            ->method( 'receive' )
            ->with( $signal );

        $factory = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\SlotFactory' );
        $factory
            ->expects( $this->any() )
            ->method( 'getSlot' )
            ->will( $this->returnValue( $slot ) );

        $dispatcher = new SignalSlot\SignalDispatcher\DefaultSignalDispatcher( $factory );
        $dispatcher->attach( get_class( $signal ), 'my_slot' );
        $dispatcher->emit( $signal );
    }

    public function testEmitSignalMultipleSlots()
    {
        $signal = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\Signal' );

        $slot = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\Slot' );
        $slot
            ->expects( $this->exactly( 2 ) )
            ->method( 'receive' )
            ->with( $signal );

        $factory = $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\SlotFactory' );
        $factory
            ->expects( $this->any() )
            ->method( 'getSlot' )
            ->will( $this->returnValue( $slot ) );

        $dispatcher = new SignalSlot\SignalDispatcher\DefaultSignalDispatcher( $factory );
        $dispatcher->attach( get_class( $signal ), 'my_slot' );
        $dispatcher->attach( get_class( $signal ), 'my_second_slot' );
        $dispatcher->emit( $signal );
    }
}
