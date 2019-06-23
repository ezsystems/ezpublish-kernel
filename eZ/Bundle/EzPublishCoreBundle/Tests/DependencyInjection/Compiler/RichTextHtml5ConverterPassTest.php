<?php

/**
 * File containing the RichTextHtml5ConverterPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RichTextHtml5ConverterPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RichTextHtml5ConverterPassTest extends AbstractCompilerPassTestCase
{
    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RichTextHtml5ConverterPass());
    }

    public function testCollectProviders()
    {
        $configurationResolver = new Definition();
        $this->setDefinition(
            'ezpublish.fieldType.ezrichtext.converter.output.xhtml5',
            $configurationResolver
        );

        $configurationProvider = new Definition();
        $configurationProvider->addTag('ezpublish.ezrichtext.converter.output.xhtml5');
        $this->setDefinition('ezrichtext.converter.test1', $configurationProvider);

        $configurationProvider = new Definition();
        $configurationProvider->addTag('ezpublish.ezrichtext.converter.output.xhtml5', ['priority' => 10]);
        $this->setDefinition('ezrichtext.converter.test2', $configurationProvider);

        $configurationProvider = new Definition();
        $configurationProvider->addTag('ezpublish.ezrichtext.converter.output.xhtml5', ['priority' => 5]);
        $this->setDefinition('ezrichtext.converter.test3', $configurationProvider);

        $configurationProvider = new Definition();
        $configurationProvider->addTag('ezpublish.ezrichtext.converter.output.xhtml5', ['priority' => 5]);
        $this->setDefinition('ezrichtext.converter.test4', $configurationProvider);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.fieldType.ezrichtext.converter.output.xhtml5',
            0,
            [
                new Reference('ezrichtext.converter.test1'),
                new Reference('ezrichtext.converter.test3'),
                new Reference('ezrichtext.converter.test4'),
                new Reference('ezrichtext.converter.test2'),
            ]
        );
    }
}
