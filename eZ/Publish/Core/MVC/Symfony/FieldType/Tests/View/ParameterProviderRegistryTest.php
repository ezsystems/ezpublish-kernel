<?php
/**
 * File containing the ParameterProviderRegistryTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\View;

use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistry;

class ParameterProviderRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistry::setParameterProvider
     * @covers eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistry::hasParameterProvider
     */
    public function testSetHasParameterProvider()
    {
        $registry = new ParameterProviderRegistry;
        $this->assertFalse( $registry->hasParameterProvider( 'foo' ) );
        $registry->setParameterProvider(
            $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\FieldType\\View\\ParameterProviderInterface' ),
            'foo'
        );
        $this->assertTrue( $registry->hasParameterProvider( 'foo' ) );
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @covers eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistry::getParameterProvider
     */
    public function testGetParameterProviderFail()
    {
        $registry = new ParameterProviderRegistry;
        $registry->getParameterProvider( 'foo' );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistry::setParameterProvider
     * @covers eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistry::getParameterProvider
     */
    public function testGetParameterProvider()
    {
        $provider = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\FieldType\\View\\ParameterProviderInterface' );
        $registry = new ParameterProviderRegistry;
        $registry->setParameterProvider( $provider, 'foo' );
        $this->assertSame( $provider, $registry->getParameterProvider( 'foo' ) );
    }
}
