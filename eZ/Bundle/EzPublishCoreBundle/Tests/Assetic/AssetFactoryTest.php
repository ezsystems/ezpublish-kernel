<?php
/**
 * File containing the AssetFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Assetic;

use eZ\Bundle\EzPublishCoreBundle\Assetic\AssetFactory;
use Symfony\Bundle\AsseticBundle\Tests\Factory\AssetFactoryTest as BaseTest;
use ReflectionObject;

class AssetFactoryTest extends BaseTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $parser;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock( '\eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $this->parser = $this->getMock(
            '\eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParserInterface'
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function testParseInputNotString()
    {
        $assetFactory = new AssetFactory(
            $this->getMock( '\Symfony\Component\HttpKernel\KernelInterface' ),
            $this->getMock( '\Symfony\Component\DependencyInjection\ContainerInterface' ),
            $this->getMock( '\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface' ),
            '/'
        );
        $assetFactory->setConfigResolver( $this->configResolver );
        $assetFactory->setDynamicSettingParser( $this->parser );

        $input = '$foo$';
        $this->parser
            ->expects( $this->once() )
            ->method( 'isDynamicSetting' )
            ->with( $input )
            ->will( $this->returnValue( true ) );
        $this->parser
            ->expects( $this->once() )
            ->method( 'parseDynamicSetting' )
            ->with( $input )
            ->will( $this->returnValue( array( 'param' => 'foo', 'namespace' => null, 'scope' => null ) ) );
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'foo', null, null )
            ->will( $this->returnValue( array( 'bar' ) ) );

        $refFactory = new ReflectionObject( $assetFactory );
        $refMethod = $refFactory->getMethod( 'parseInput' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $assetFactory, $input );
    }
}
