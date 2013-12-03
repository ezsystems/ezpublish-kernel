<?php
/**
 * File containing the ChainConfigResolverPassTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainConfigResolverPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTest;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ChainConfigResolverPassTest extends AbstractCompilerPassTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition( 'ezpublish.config.resolver.chain', new Definition() );
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new ChainConfigResolverPass() );
    }

    /**
     * @param int|null $declaredPriority
     * @param int $expectedPriority
     *
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainConfigResolverPass::process
     * @dataProvider addResolverProvider
     */
    public function testAddResolver( $declaredPriority, $expectedPriority )
    {
        $resolverDef = new Definition();
        $serviceId = 'some_service_id';
        if ( $declaredPriority !== null )
        {
            $resolverDef->addTag( 'ezpublish.config.resolver', array( 'priority' => $declaredPriority ) );
        }
        else
        {
            $resolverDef->addTag( 'ezpublish.config.resolver' );
        }

        $this->setDefinition( $serviceId, $resolverDef );
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.config.resolver.chain',
            'addResolver',
            array( new Reference( $serviceId ), $expectedPriority )
        );
    }

    public function addResolverProvider()
    {
        return array(
            array( null, 0 ),
            array( 0, 0 ),
            array( 57, 57 ),
            array( -23, -23 ),
            array( -255, -255 ),
            array( -256, -255 ),
            array( -1000, -255 ),
            array( 255, 255 ),
            array( 256, 255 ),
            array( 1000, 255 ),
        );
    }
}
