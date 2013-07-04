<?php
/**
 * File containing the SignalSlotPassTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\SignalSlotPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use PHPUnit_Framework_TestCase;

class SignalSlotPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $dispatcherDef = new Definition();
        $slotDef = new Definition();
        $signalIdentifier = 'FooSignal';
        $slotDef->addTag( 'ezpublish.api.slot', array( 'signal' => $signalIdentifier ) );

        $containerBuilder = new ContainerBuilder();
        $slotId = 'acme.foo_slot';
        $containerBuilder->addDefinitions(
            array(
                $slotId => $slotDef,
                'ezpublish.signalslot.signal_dispatcher' => $dispatcherDef
            )
        );

        $pass = new SignalSlotPass();
        $pass->process( $containerBuilder );
        $this->assertTrue( $dispatcherDef->hasMethodCall( 'attach' ) );
        $calls = $dispatcherDef->getMethodCalls();
        list( $method, $arguments ) = $calls[0];
        $this->assertSame( 'attach', $method );
        list( $signal, $serviceId ) = $arguments;
        $this->assertSame( $signalIdentifier, $signal );
        $this->assertSame( $slotId, $serviceId );
    }

    /**
     * @expectedException \LogicException
     */
    public function testProcessNoSignal()
    {
        $slotDef = new Definition();
        $slotDef->addTag( 'ezpublish.api.slot', array() );

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            array(
                'acme.foo_slot' => $slotDef,
                'ezpublish.signalslot.signal_dispatcher' => new Definition()
            )
        );

        $pass = new SignalSlotPass();
        $pass->process( $containerBuilder );
    }
}
