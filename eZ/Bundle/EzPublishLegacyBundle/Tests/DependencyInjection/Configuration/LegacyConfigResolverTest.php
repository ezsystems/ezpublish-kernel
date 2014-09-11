<?php
/**
 * File containing the LegacyConfigResolverTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Tests\DependencyInjection\Configuration;

use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration\LegacyConfigResolver;
use PHPUnit_Framework_TestCase;
use eZ\Publish\Core\MVC\Exception\ParameterNotFoundException;

class LegacyConfigResolverTest extends PHPUnit_Framework_TestCase
{
    const DEFAULT_NAMESPACE = 'site';

    /**
     * @var \eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration\LegacyConfigResolver
     */
    private $resolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $legacyKernel;

    protected function setUp()
    {
        parent::setUp();
        $legacyKernel = $this->legacyKernel = $this->getMock( 'ezpKernelHandler' );
        $kernelClosure = function () use ( $legacyKernel )
        {
            return $legacyKernel;
        };

        $this->resolver = new LegacyConfigResolver( $kernelClosure, self::DEFAULT_NAMESPACE );
    }

    public function testGetSetDefaultNamespace()
    {
        $this->assertSame( self::DEFAULT_NAMESPACE, $this->resolver->getDefaultNamespace() );
        $ns = 'image';
        $this->resolver->setDefaultNamespace( $ns );
        $this->assertSame( $ns, $this->resolver->getDefaultNamespace() );
    }

    public function testHasParameterInvalidParam()
    {
        $this->legacyKernel
            ->expects( $this->never() )
            ->method( 'runCallback' );
        $this->assertFalse( $this->resolver->hasParameter( 'this_is_invalid' ) );
    }

    /**
     * @dataProvider hasParameterProvider
     */
    public function testHasParameter( $paramName, $namespace, $expected )
    {
        $namespace = $namespace ?: self::DEFAULT_NAMESPACE;
        $this->legacyKernel
            ->expects( $this->once() )
            ->method( 'runCallback' )
            ->will( $this->returnValue( $expected ) );
        $this->assertSame( $expected, $this->resolver->hasParameter( $paramName, $namespace ) );
    }

    public function hasParameterProvider()
    {
        return array(
            array( 'Foo.Bar', null, true ),
            array( 'Foo.Bar.baz', null, false ),
            array( 'Foo.Babar', 'foo.ini', true ),
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException
     */
    public function testGetParameterInvalidParam()
    {
        $this->legacyKernel
            ->expects( $this->never() )
            ->method( 'runCallback' );
        $this->resolver->getParameter( 'this_is_invalid' );
    }

    /**
     * @dataProvider getParameterProvider
     */
    public function testGetParameter( $paramName, $namespace, $value )
    {
        $this->legacyKernel
            ->expects( $this->once() )
            ->method( 'runCallback' )
            ->will( $this->returnValue( $value ) );

        $this->assertSame( $value, $this->resolver->getParameter( $paramName, $namespace ) );
    }

    public function getParameterProvider()
    {
        return array(
            array( 'Foo.Bar', null, 'something' ),
            array( 'Foo.Bar.baz', null, array( 'blabla' ) ),
            array( 'Foo.Babar', 'foo.ini', 'enabled' ),
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException
     */
    public function testGetNonExistentParameter()
    {
        $paramName = 'Foo.Bar';
        $namespace = 'foo';
        $this->legacyKernel
            ->expects( $this->once() )
            ->method( 'runCallback' )
            ->will( $this->throwException( new ParameterNotFoundException( $paramName, "$namespace.ini" ) ) );

        $this->resolver->getParameter( $paramName, $namespace );
    }
}
