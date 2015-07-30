<?php

/**
 * File containing the XmlTextConverterPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\XmlTextConverterPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class XmlTextConverterPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $html5ConvertDef = new Definition();
        $container->setDefinition('ezpublish.fieldType.ezxmltext.converter.html5', $html5ConvertDef);

        $preConverterDef = new Definition();
        $preConverterDef->addTag('ezpublish.ezxml.converter');
        $container->setDefinition('foo.converter', $preConverterDef);

        $this->assertFalse($html5ConvertDef->hasMethodCall('addPreConverter'));
        $pass = new XmlTextConverterPass();
        $pass->process($container);
        $this->assertTrue($html5ConvertDef->hasMethodCall('addPreConverter'));
        $calls = $html5ConvertDef->getMethodCalls();
        $this->assertSame(1, count($calls));
        list($method, $arguments) = $calls[0];
        $this->assertSame('addPreConverter', $method);
        $this->assertInstanceOf('Symfony\\Component\\DependencyInjection\\Reference', $arguments[0]);
        $this->assertSame('foo.converter', (string)$arguments[0]);
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new XmlTextConverterPass());
    }

    public function testAddPreConverter()
    {
        $this->setDefinition('ezpublish.fieldType.ezxmltext.converter.html5', new Definition());
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag('ezpublish.ezxml.converter');
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.fieldType.ezxmltext.converter.html5',
            'addPreConverter',
            array(new Reference($serviceId))
        );
    }

    public function testSortConverterIds()
    {
        $container = new ContainerBuilder();
        $html5ConvertDef = $this->getMock(
            'Symfony\\Component\\DependencyInjection\\Definition',
            array(
                'addMethodCall',
            )
        );
        $container->setDefinition('ezpublish.fieldType.ezxmltext.converter.html5', $html5ConvertDef);

        $preConverterDef1 = new Definition();
        $preConverterDef1->addTag('ezpublish.ezxml.converter', array('priority' => 10));
        $container->setDefinition('foo.converter1', $preConverterDef1);

        $preConverterDef2 = new Definition();
        $preConverterDef2->addTag('ezpublish.ezxml.converter', array('priority' => 5));
        $container->setDefinition('foo.converter2', $preConverterDef2);

        $preConverterDef3 = new Definition();
        $preConverterDef3->addTag('ezpublish.ezxml.converter', array('priority' => 15));
        $container->setDefinition('foo.converter3', $preConverterDef3);

        $html5ConvertDef
            ->expects($this->at(0))
            ->method('addMethodCall')
            ->with(
                'addPreConverter',
                array(new Reference('foo.converter3'))
            );

        $html5ConvertDef
            ->expects($this->at(1))
            ->method('addMethodCall')
            ->with(
                'addPreConverter',
                array(new Reference('foo.converter1'))
            );

        $html5ConvertDef
            ->expects($this->at(2))
            ->method('addMethodCall')
            ->with(
                'addPreConverter',
                array(new Reference('foo.converter2'))
            );

        $pass = new XmlTextConverterPass();
        $pass->process($container);
    }
}
