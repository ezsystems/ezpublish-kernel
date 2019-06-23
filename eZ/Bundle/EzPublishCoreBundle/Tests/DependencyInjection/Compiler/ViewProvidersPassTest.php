<?php

/**
 * File containing the BlockViewPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ViewProvidersPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ViewProvidersPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition('ezpublish.view_provider.registry', new Definition());
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ViewProvidersPass());
    }

    /**
     * @dataProvider addViewProviderProvider
     */
    public function testAddViewProvider($declaredPriority, $expectedPriority)
    {
        $def = new Definition();

        $attributes = ['type' => 'Test\View'];
        if ($declaredPriority !== null) {
            $attributes['priority'] = $declaredPriority;
        }
        $def->addTag('ezpublish.view_provider', $attributes);
        $serviceId = 'service_id';
        $this->setDefinition($serviceId, $def);

        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.view_provider.registry',
            'setViewProviders',
            [
                ['Test\View' => [new Reference($serviceId)]],
            ]
        );
    }

    public function addViewProviderProvider()
    {
        return [
            [null, 0],
            [0, 0],
            [57, 57],
            [-23, -23],
            [-255, -255],
            [-256, -255],
            [-1000, -255],
            [255, 255],
            [256, 255],
            [1000, 255],
        ];
    }
}
