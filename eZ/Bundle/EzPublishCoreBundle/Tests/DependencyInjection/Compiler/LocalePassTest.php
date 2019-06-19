<?php

/**
 * File containing the LocalePassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LocalePass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class LocalePassTest extends AbstractCompilerPassTestCase
{
    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new LocalePass());
    }

    public function testLocaleListener()
    {
        $this->setDefinition('locale_listener', new Definition());
        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'locale_listener',
            'setConfigResolver',
            [new Reference('ezpublish.config.resolver')]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'locale_listener',
            'setLocaleConverter',
            [new Reference('ezpublish.locale.converter')]
        );
    }
}
