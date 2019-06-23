<?php

/**
 * File containing the SignalSlotPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Search;

use eZ\Publish\Core\Base\Container\Compiler\Search\SignalSlotPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SignalSlotPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition('ezpublish.signalslot.signal_dispatcher', new Definition());
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SignalSlotPass());
    }

    public function testAttachSignal()
    {
        $signal = 'signal_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag('ezpublish.search.slot', ['signal' => $signal]);
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.signalslot.signal_dispatcher',
            'attach',
            [$signal, new Reference($serviceId)]
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
        $def->addTag('ezpublish.search.slot');
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.signalslot.signal_dispatcher',
            'attach',
            [$signal, new Reference($serviceId)]
        );
    }
}
