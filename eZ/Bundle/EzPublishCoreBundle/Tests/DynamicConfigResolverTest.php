<?php
/**
 * File containing the DynamicConfigResolverTest class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess,
    eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver;

class DynamicConfigResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    private $siteAccess;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $containerMock;

    protected function setUp()
    {
        parent::setUp();
        $this->siteAccess = new SiteAccess( 'test' );
        $this->containerMock = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver::_construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver::getUndefinedStrategy
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver::setUndefinedStrategy
     */
    public function testGetSetUndefinedStrategy()
    {
        $strategy = DynamicConfigResolver::UNDEFINED_STRATEGY_NULL;
        $resolver = new DynamicConfigResolver(
            $this->siteAccess,
            $this->containerMock,
            'ezsettings',
            $strategy
        );
        $this->assertSame( $strategy, $resolver->getUndefinedStrategy() );
        $resolver->setUndefinedStrategy( DynamicConfigResolver::UNDEFINED_STRATEGY_EXCEPTION );
        $this->assertSame( DynamicConfigResolver::UNDEFINED_STRATEGY_EXCEPTION, $resolver->getUndefinedStrategy() );

        $this->assertSame( $defaultNS, $resolver->getDefaultNamespace() );
        $resolver->setDefaultNamespace( 'anotherNamespace' );
        $this->assertSame( 'anotherNamespace', $resolver->getDefaultNamespace() );
    }

    /**
     * @expectedException \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver::_construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver::getParameter
     */
    public function testGetParameterFailedWithException()
    {
        $resolver = new DynamicConfigResolver(
            $this->siteAccess,
            $this->containerMock,
            'ezsettings',
            DynamicConfigResolver::UNDEFINED_STRATEGY_EXCEPTION
        );
        $resolver->getParameter( 'foo' );
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver::_construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver::getParameter
     */
    public function testGetParameterFailedNull()
    {
        $resolver = new DynamicConfigResolver(
            $this->siteAccess,
            $this->containerMock,
            'ezsettings',
            DynamicConfigResolver::UNDEFINED_STRATEGY_NULL
        );
        $this->assertNull( $resolver->getParameter( 'foo' ) );
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver::_construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver::getParameter
     */
    public function testGetParameterGlobalScope()
    {
        $this->markTestIncomplete();
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver::_construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver::getParameter
     */
    public function testGetParameterRelativeScope()
    {
        $this->markTestIncomplete();
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver::_construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver::getParameter
     */
    public function testGetParameterDefaultScope()
    {
        $this->markTestIncomplete();
    }
}
