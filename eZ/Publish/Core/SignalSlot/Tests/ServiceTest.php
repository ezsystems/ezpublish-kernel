<?php
/**
 * File containing the ServiceTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\SignalSlot\Tests\ServiceTest;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use \PHPUnit_Framework_TestCase;

abstract class ServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * Returns a mock of the aggregated service
     */
    abstract protected function getServiceMock();

    /**
     * Returns an instance of the SignalSlot service to test
     *
     * @param mixed $innerService mock of the inner service used by the signal 
     * slot one used to test whether the original method is called is correctly 
     * called.
     * @param eZ\Publish\Core\SignalSlot\SignalDispatcher $dispatcher mock of 
     * the dispatcher used to test whether the emit method is correctly called
     *
     * @return an instance of the SignalSlot service
     */
    abstract protected function getSignalSlotService( $innerService, SignalDispatcher $dispatcher );

    /**
     * @dataProvider serviceProvider
     *
     * Tests that:
     * - the original service method is called with the exact same arguments
     * - the signal is emitted with the correct signal object containing the 
     *   expected attributes/values
     * - the returned value from the original service method is returned 
     *   by the method from the signal slot service
     */
    public function testService(
        $method, $parameters, $return,
        $emitNr, $signalClass = '', array $signalAttr = null
    )
    {
        $innerService = $this->getServiceMock();
        $innerService->expects( $this->once() )
            ->method( $method )
            ->will(
                $this->returnValueMap(
                    array(
                        array_merge( $parameters, array( $return ) )
                    )
                )
            );

        $dispatcher = $this->getMock( 'eZ\\Publish\\Core\\SignalSlot\\SignalDispatcher' );
        $that = $this;
        $d = $dispatcher->expects( $this->exactly( $emitNr ) )
            ->method( 'emit' );
        if ( $emitNr && $signalClass && $signalAttr )
        {
            $d->with(
                $this->callback(
                    function ( $signal ) use ( $that, $signalClass, $signalAttr )
                    {
                        if ( !$signal instanceof $signalClass )
                        {
                            $that->fail(
                                "The signal is not an instance of $signalClass"
                            );
                            return false;
                        }
                        foreach ( $signalAttr as $attr => $val )
                        {
                            if ( $signal->{$attr} !== $val )
                            {
                                $that->fail(
                                    "The attribute '{$attr}' of the signal does not have the correct value '{$val}'"
                                );
                                return false;
                            }
                        }
                        return true;
                    }
                )
            );
        }
        $service = $this->getSignalSlotService( $innerService, $dispatcher );
        $result = call_user_func_array( array( $service, $method ), $parameters );

        $this->assertTrue( $result === $return );
    }
}
