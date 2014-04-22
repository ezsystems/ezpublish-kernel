<?php
/**
 * File containing the SignalSlotPassTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Storage\Solr;

use eZ\Publish\Core\Base\Container\Compiler\Storage\Solr\SignalSlotPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTest;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SignalSlotPassTest extends AbstractCompilerPassTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition( 'ezpublish.signalslot.signal_dispatcher', new Definition() );
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new SignalSlotPass() );
    }

    public function testAttachSignal()
    {
        $signal = 'signal_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.persistence.solr.slot', array( 'signal' => $signal ) );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.signalslot.signal_dispatcher',
            'attach',
            array( $signal, new Reference( $serviceId ) )
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testAttachSignalNoAlias()
    {
        $signal = 'signal_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.persistence.solr.slot' );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.signalslot.signal_dispatcher',
            'attach',
            array( $signal, new Reference( $serviceId ) )
        );
    }
}
