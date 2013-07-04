<?php
/**
 * File containing the IOFactoryTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\IOFactory;

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

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $this->mimeDetector = $this->getMock( 'eZ\\Publish\\SPI\\IO\\MimeTypeDetector' );
    }

    public function testGetService()
    {
        $prefixSetting = 'foo';
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( $prefixSetting )
            ->will( $this->returnValue( 'my_prefix' ) );
        $factory = new IOFactory( $this->configResolver, $this->mimeDetector );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\IO\\IOService',
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
        $factory = new IOFactory( $this->configResolver, $this->mimeDetector );

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
        $factory = new IOFactory( $this->configResolver, $this->mimeDetector );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\IO\\Handler',
            $factory->getHandler( $handlerClass, $directorySettings )
        );
    }
}
