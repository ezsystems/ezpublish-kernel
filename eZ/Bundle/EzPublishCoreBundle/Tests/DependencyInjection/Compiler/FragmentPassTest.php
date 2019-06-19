<?php

/**
 * File containing the FragmentPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\FragmentPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FragmentPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FragmentPass());
    }

    public function testProcess()
    {
        $inlineClass = 'Foo';
        $this->container->setParameter('ezpublish.decorated_fragment_renderer.inline.class', $inlineClass);
        $inlineRendererDef = new Definition($inlineClass);
        $inlineRendererDef->addTag('kernel.fragment_renderer');
        $esiRendererDef = new Definition();
        $esiRendererDef->addTag('kernel.fragment_renderer');
        $hincludeRendererDef = new Definition();
        $hincludeRendererDef->addTag('kernel.fragment_renderer');

        $decoratedFragmentRendererDef = new Definition();
        $decoratedFragmentRendererDef->setAbstract(true);

        $this->setDefinition('fragment.listener', new Definition());
        $this->setDefinition('fragment.renderer.inline', $inlineRendererDef);
        $this->setDefinition('fragment.renderer.esi', $esiRendererDef);
        $this->setDefinition('fragment.renderer.hinclude', $hincludeRendererDef);
        $this->setDefinition('ezpublish.decorated_fragment_renderer', $decoratedFragmentRendererDef);

        $this->compile();

        $this->assertTrue($this->container->hasDefinition('fragment.listener'));
        $fragmentListenerDef = $this->container->getDefinition('fragment.listener');

        $factoryArray = $fragmentListenerDef->getFactory();
        $this->assertInstanceOf(Reference::class, $factoryArray[0]);
        $this->assertEquals('buildFragmentListener', $factoryArray[1]);
        $this->assertEquals('ezpublish.fragment_listener.factory', $factoryArray[0]);

        $this->assertTrue($this->container->hasDefinition('fragment.renderer.inline.inner'));
        $this->assertSame($inlineRendererDef, $this->container->getDefinition('fragment.renderer.inline.inner'));
        $this->assertFalse($inlineRendererDef->isPublic());
        $this->assertTrue($this->container->hasDefinition('fragment.renderer.esi.inner'));
        $this->assertSame($esiRendererDef, $this->container->getDefinition('fragment.renderer.esi.inner'));
        $this->assertFalse($esiRendererDef->isPublic());
        $this->assertTrue($this->container->hasDefinition('fragment.renderer.hinclude.inner'));
        $this->assertSame($hincludeRendererDef, $this->container->getDefinition('fragment.renderer.hinclude.inner'));
        $this->assertFalse($hincludeRendererDef->isPublic());

        $this->assertContainerBuilderHasServiceDefinitionWithParent('fragment.renderer.inline', 'ezpublish.decorated_fragment_renderer');
        $decoratedInlineDef = $this->container->getDefinition('fragment.renderer.inline');
        $this->assertSame(['kernel.fragment_renderer' => [[]]], $decoratedInlineDef->getTags());
        $this->assertEquals(
            [new Reference('fragment.renderer.inline.inner')],
            $decoratedInlineDef->getArguments()
        );
        $this->assertSame($inlineClass, $decoratedInlineDef->getClass());

        $this->assertContainerBuilderHasServiceDefinitionWithParent('fragment.renderer.esi', 'ezpublish.decorated_fragment_renderer');
        $decoratedEsiDef = $this->container->getDefinition('fragment.renderer.esi');
        $this->assertSame(['kernel.fragment_renderer' => [[]]], $decoratedEsiDef->getTags());
        $this->assertEquals(
            [new Reference('fragment.renderer.esi.inner')],
            $decoratedEsiDef->getArguments()
        );

        $this->assertContainerBuilderHasServiceDefinitionWithParent('fragment.renderer.hinclude', 'ezpublish.decorated_fragment_renderer');
        $decoratedHincludeDef = $this->container->getDefinition('fragment.renderer.hinclude');
        $this->assertSame(['kernel.fragment_renderer' => [[]]], $decoratedHincludeDef->getTags());
        $this->assertEquals(
            [new Reference('fragment.renderer.hinclude.inner')],
            $decoratedHincludeDef->getArguments()
        );
    }
}
