<?php
/**
 * File containing the CacheFactoryTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\CacheFactory;

class CacheFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $this->container = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
    }

    /**
     * @return array
     */
    function providerGetService()
    {
        return array(
            array( 'default', 'stash.default_cache' ),
            array( 'ez_site1', 'stash.ez_site1_cache' ),
            array( 'xyZ', 'stash.xyZ_cache' )
        );
    }

    /**
     * @dataProvider providerGetService
     */
    public function testGetService( $name, $expected )
    {
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'cache_pool_name' )
            ->will( $this->returnValue( $name ) );

        $this->container
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( $expected )
            ->will( $this->returnValue( false ) );

        $factory = new CacheFactory;

        $this->assertFalse( $factory->getCachePool( $this->configResolver, $this->container ) );
    }
}
