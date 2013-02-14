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
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * @group signalSlot
 * @covers \eZ\Publish\Core\SignalSlot\SlotFactory\ContainerSlotFactory
 */
class ContainerSlotFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function providerForFactoryTests()
    {
        return array(
            array( array( "slot1" => true, "slot2" => true ) ),
            array(
                array(
                    'slot1' => $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\Slot' ),
                    'slot2' => $this->getMock( '\\eZ\\Publish\\Core\\SignalSlot\\Slot' )
                )
            ),
        );
    }

    /**
     * @dataProvider providerForFactoryTests
     */
    public function testValidSlot( $slots )
    {
        $factory = $this->setUpFactory( $slots );
        foreach ( $slots as $slotIdentifier => $slotValue )
            $this->assertEquals( $slotValue, $factory->getSlot( $slotIdentifier ) );
    }

    /**
     * @dataProvider providerForFactoryTests
     */
    public function testInValidSlot( $slots )
    {
        $factory = $this->setUpFactory( $slots, false );
        foreach ( array_keys( $slots ) as $slotIdentifier )
        {
            try
            {
                $factory->getSlot( $slotIdentifier );
                $this->fail( 'expected NotFoundException ' );
            }
            catch ( NotFoundException $e )
            {
            }
        }
    }

    private function setUpFactory( $slots, $hasReturnValue = true )
    {
        $container = $this->getMock( '\\Symfony\\Component\\DependencyInjection\\ContainerInterface' );
        $factory = new SignalSlot\SlotFactory\ContainerSlotFactory( $container );

        $i = 0;
        foreach ( $slots as $slotIdentifier => $slotValue )
        {
            $container
                ->expects( $this->at( $i ) )
                ->method( 'has' )
                ->with( $slotIdentifier )
                ->will( $this->returnValue( $hasReturnValue ) );

            $i++;
            // No calls to 'get' are done if 'has' returns false
            if ( $hasReturnValue === false )
                continue;

            $container
                ->expects( $this->at( $i ) )
                ->method( 'get' )
                ->with( $slotIdentifier )
                ->will( $this->returnValue( $slotValue ) );
            ++$i;
        }
        return $factory;
    }
}

