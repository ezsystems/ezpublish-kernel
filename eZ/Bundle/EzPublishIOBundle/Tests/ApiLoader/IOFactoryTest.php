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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mimeDetector;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $this->mimeDetector = $this->getMock( 'eZ\\Publish\\SPI\\IO\\MimeTypeDetector' );
        $this->container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
    }

    public function testGetService()
    {
        $prefixSetting = 'foo';
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( $prefixSetting )
            ->will( $this->returnValue( 'my_prefix' ) );
        $factory = new IOFactory( $this->container, $this->configResolver, $this->mimeDetector );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\IO\\IOServiceInterface',
            $factory->getService( $this->getMock( 'eZ\\Publish\\Core\\IO\\Handler' ), $prefixSetting )
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
        $factory = new IOFactory( $this->container, $this->configResolver, $this->mimeDetector );

        $handlerClass = get_class( $this->getMock( 'eZ\\Publish\\Core\\IO\\Handler' ) );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\IO\\Handler',
            $factory->getHandler( $handlerClass, $directorySetting )
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

        $handlerClass = get_class( $this->getMock( 'eZ\\Publish\\Core\\IO\\Handler' ) );
        $factory = new IOFactory( $this->container, $this->configResolver, $this->mimeDetector );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\IO\\Handler',
            $factory->getHandler( $handlerClass, $directorySettings )
        );
    }
}
