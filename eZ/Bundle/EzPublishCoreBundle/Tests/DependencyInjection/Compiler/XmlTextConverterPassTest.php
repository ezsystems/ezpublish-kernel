<?php
/**
 * File containing the XmlTextConverterPassTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\XmlTextConverterPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTest;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class XmlTextConverterPassTest extends AbstractCompilerPassTest
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $html5ConvertDef = new Definition();
        $container->setDefinition( 'ezpublish.fieldType.ezxmltext.converter.html5', $html5ConvertDef );

        $preConverterDef = new Definition();
        $preConverterDef->addTag( 'ezpublish.ezxml.converter' );
        $container->setDefinition( 'foo.converter', $preConverterDef );

        $this->assertFalse( $html5ConvertDef->hasMethodCall( 'addPreConverter' ) );
        $pass = new XmlTextConverterPass();
        $pass->process( $container );
        $this->assertTrue( $html5ConvertDef->hasMethodCall( 'addPreConverter' ) );
        $calls = $html5ConvertDef->getMethodCalls();
        $this->assertSame( 1, count( $calls ) );
        list( $method, $arguments ) = $calls[0];
        $this->assertSame( 'addPreConverter', $method );
        $this->assertInstanceOf( 'Symfony\\Component\\DependencyInjection\\Reference', $arguments[0] );
        $this->assertSame( 'foo.converter', (string)$arguments[0] );
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new XmlTextConverterPass() );
    }

    public function testAddPreConverter()
    {
        $this->setDefinition( 'ezpublish.fieldType.ezxmltext.converter.html5', new Definition() );
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag( 'ezpublish.ezxml.converter' );
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.fieldType.ezxmltext.converter.html5',
            'addPreConverter',
            array( new Reference( $serviceId ) )
        );
    }
}
