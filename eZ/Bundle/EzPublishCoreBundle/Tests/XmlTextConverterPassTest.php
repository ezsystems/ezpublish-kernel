<?php
/**
 * File containing the XmlTextConverterPassTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\XmlTextConverterPass;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class XmlTextConverterPassTest extends PHPUnit_Framework_TestCase
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
}
