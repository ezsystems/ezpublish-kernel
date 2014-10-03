<?php
/**
 * File containing the IOFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishIOBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishIOBundle\ApiLoader\IOFactory;

class IOFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\SPI\IO\MimeTypeDetector
     */
    private $mimeDetector;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var IOFactory
     */
    private $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $this->mimeDetector = $this->getMock( 'eZ\Publish\SPI\IO\MimeTypeDetector' );
        $this->container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
        $this->factory = new IOFactory( $this->configResolver, $this->mimeDetector );
        $this->factory->setContainer( $this->container );
        $this->factory->setHandlersMap( array( 'configured_handler' => 'my_io_handler' ) );
    }

    public function testGetService()
    {
        $prefixSetting = 'foo';
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( $prefixSetting )
            ->will( $this->returnValue( 'my_prefix' ) );
        $this->assertInstanceOf(
            'eZ\Publish\Core\IO\IOServiceInterface',
            $this->factory->getService( $this->getMock( 'eZ\Publish\Core\IO\Handler' ), $prefixSetting )
        );
    }

    public function testGetHandlerStorageDirectoryAsString()
    {
        $directorySetting = 'foo';
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( $directorySetting )
            ->will( $this->returnValue( 'something' ) );
        $handlerClass = get_class( $this->getMock( 'eZ\Publish\Core\IO\Handler' ) );
        $this->assertInstanceOf(
            'eZ\Publish\Core\IO\Handler',
            $this->factory->getHandler( $handlerClass, $directorySetting )
        );
    }

    public function testGetHandlerStorageDirectoryAsArray()
    {
        $directorySettings = array( 'foo', 'bar' );
        $this->configResolver
            ->expects( $this->exactly( count( $directorySettings ) ) )
            ->method( 'getParameter' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( $directorySettings[0], null, null, 'folder' ),
                        array( $directorySettings[1], null, null, 'subfolder' ),
                    )
                )
            );

        $handlerClass = get_class( $this->getMock( 'eZ\Publish\Core\IO\Handler' ) );
        $this->assertInstanceOf(
            'eZ\Publish\Core\IO\Handler',
            $this->factory->getHandler( $handlerClass, $directorySettings )
        );
    }

    public function testBuildConfiguredHandler()
    {
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'handler', 'ez_io' )
            ->will( $this->returnValue( 'configured_handler' ) );

        $ioHandler = $this->getMock( 'eZ\Publish\Core\IO\Handler' );
        $this->container
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'my_io_handler' )
            ->will( $this->returnValue( $ioHandler ) );

        $this->assertSame(
            $ioHandler,
            $this->factory->buildConfiguredHandler()
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage No IO handler found for alias badly_configured_handler
     */
    public function testBuildConfiguredHandlerNotFound()
    {
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'handler', 'ez_io' )
            ->will( $this->returnValue( 'badly_configured_handler' ) );

        $this->container
            ->expects( $this->never() )
            ->method( 'get' )
            ->with( 'badly_configured_handler' );

        $this->factory->buildConfiguredHandler();
    }
}
