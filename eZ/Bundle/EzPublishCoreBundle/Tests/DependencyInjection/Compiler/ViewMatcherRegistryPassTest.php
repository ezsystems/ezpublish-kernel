<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ViewMatcherRegistryPass;
use eZ\Bundle\EzPublishCoreBundle\Matcher\ViewMatcherRegistry;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ViewMatcherRegistryPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setDefinition(ViewMatcherRegistry::class, new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ViewMatcherRegistryPass());
    }

    public function testSetMatcher(): void
    {
        $def = new Definition();
        $def->addTag(ViewMatcherRegistryPass::MATCHER_TAG);
        $serviceId = 'service_id';
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            ViewMatcherRegistry::class,
            'setMatcher',
            [
                $serviceId,
                new Reference($serviceId),
            ]
        );
    }
}
