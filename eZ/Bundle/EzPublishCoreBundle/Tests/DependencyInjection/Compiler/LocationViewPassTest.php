<?php

/**
 * File containing the LocationViewPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LocationViewPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class LocationViewPassTest extends AbstractCompilerPassTestCase
{
    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new LocationViewPass());
    }

    public function testAddViewProvider()
    {
        $def = new Definition();
        $def->addTag(LocationViewPass::VIEW_PROVIDER_IDENTIFIER, ['priority' => 12]);
        $serviceId = 'service_id';
        $this->setDefinition($serviceId, $def);

        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $serviceId,
            'ezpublish.view_provider',
            ['priority' => 12, 'type' => LocationViewPass::VIEW_TYPE]
        );
    }
}
