<?php
/**
 * File containing the FragmentPassTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\FragmentPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTest;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FragmentPassTest extends AbstractCompilerPassTest
{
    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new FragmentPass() );
    }

    public function testProcess()
    {
        $esiRendererDef = new Definition();
        $esiRendererMethodCalls = array( array( 'setFoo', array( 'arg1', 'arg2' ) ) );
        $esiRendererDef->setMethodCalls( $esiRendererMethodCalls );
        $hincludeRendererDef = new Definition();
        $hincludeRendererMethodCalls = array( array( 'setBar', array( 'some', 'thing' ) ) );
        $hincludeRendererDef->setMethodCalls( $hincludeRendererMethodCalls );

        $this->setDefinition( 'fragment.listener', new Definition() );
        $this->setDefinition( 'fragment.renderer.esi', $esiRendererDef );
        $this->setDefinition( 'fragment.renderer.hinclude', $hincludeRendererDef );

        $this->compile();

        $this->assertTrue( $this->container->hasDefinition( 'fragment.listener' ) );
        $fragmentListenerDef = $this->container->getDefinition( 'fragment.listener' );
        $this->assertSame( 'ezpublish.fragment_listener.factory', $fragmentListenerDef->getFactoryService() );
        $this->assertSame( 'buildFragmentListener', $fragmentListenerDef->getFactoryMethod() );

        array_unshift(
            $esiRendererMethodCalls,
            array(
                'setSiteAccess',
                array( new Reference( 'ezpublish.siteaccess' ) )
            )
        );
        $this->assertEquals( $esiRendererMethodCalls, $this->container->getDefinition( 'fragment.renderer.esi' )->getMethodCalls() );

        array_unshift(
            $hincludeRendererMethodCalls,
            array(
                'setSiteAccess',
                array( new Reference( 'ezpublish.siteaccess' ) )
            )
        );
        $this->assertEquals( $hincludeRendererMethodCalls, $this->container->getDefinition( 'fragment.renderer.hinclude' )->getMethodCalls() );
    }
}
