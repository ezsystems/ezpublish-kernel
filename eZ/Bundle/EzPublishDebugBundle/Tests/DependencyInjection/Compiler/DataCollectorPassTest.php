<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishDebugBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishDebugBundle\DependencyInjection\Compiler\DataCollectorPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DataCollectorPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition('ezpublish_debug.data_collector', new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DataCollectorPass());
    }

    public function testAddCollector()
    {
        $panelTemplate = 'panel.html.twig';
        $toolbarTemplate = 'toolbar.html.twig';
        $definition = new Definition();
        $definition->addTag(
            'ezpublish_data_collector',
            ['panelTemplate' => $panelTemplate, 'toolbarTemplate' => $toolbarTemplate]
        );

        $serviceId = 'service_id';
        $this->setDefinition($serviceId, $definition);
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish_debug.data_collector',
            'addCollector',
            [new Reference($serviceId), $panelTemplate, $toolbarTemplate]
        );
    }
}
