<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\PlaceholderProviderPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class PlaceholderProviderPassTest extends AbstractCompilerPassTestCase
{
    const PROVIDER_ID = 'provider.id';
    const PROVIDER_TYPE = 'provider.test';

    protected function setUp()
    {
        parent::setUp();

        $this->setDefinition(PlaceholderProviderPass::REGISTRY_DEFINITION_ID, new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new PlaceholderProviderPass());
    }

    public function testAddProvider()
    {
        $definition = new Definition();
        $definition->addTag(PlaceholderProviderPass::TAG_NAME, ['type' => self::PROVIDER_TYPE]);

        $this->setDefinition(self::PROVIDER_ID, $definition);
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            PlaceholderProviderPass::REGISTRY_DEFINITION_ID,
            'addProvider',
            [self::PROVIDER_TYPE, new Reference(self::PROVIDER_ID)]
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddProviderWithoutType()
    {
        $definition = new Definition();
        $definition->addTag(PlaceholderProviderPass::TAG_NAME);

        $this->setDefinition(self::PROVIDER_ID, $definition);
        $this->compile();
    }
}
