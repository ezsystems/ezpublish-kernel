<?php
/**
 * File containing a Io Handler test
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Tests\Handler;

use eZ\Publish\Core\IO\Handler\Dispatcher;
use eZ\Publish\Core\IO\Handler\InMemory as InMemory;
use eZ\Publish\Core\IO\Tests\Handler\Base as BaseHandlerTest;
use eZ\Publish\SPI\IO\BinaryFile;

/**
 * Handler test
 */
class DispatcherTest extends BaseHandlerTest
{
    /**
     * @var \eZ\Publish\SPI\IO\Handler
     */
    protected $defaultBackend;

    /**
     * @var \eZ\Publish\SPI\IO\Handler
     */
    protected $alternativeBackend;

    public function setUp()
    {
        self::markTestSkipped();
    }

    /**
     * @return \eZ\Publish\Core\IO\Handler
     */
    protected function getIOHandler()
    {
        $this->defaultBackend = new InMemory();
        $this->alternativeBackend = new InMemory();
        return new Dispatcher(
            $this->defaultBackend,
            array(
                array(
                    'handler' => $this->alternativeBackend,
                    // match conditions:
                    'prefix' => 'var/test/',
                    'suffix' => '.gif,.jpg',
                    'contains' => 'image-versioned'
                )
            )
        );
    }

    /**
     * Test that file is created in default handler
     * @covers \eZ\Publish\Core\IO\Handler\Dispatcher::__construct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testDispatcherInvalidAlternativeHandlerParam()
    {
        new Dispatcher(
            $this->defaultBackend,
            array()
        );
    }

    /**
     * Test that file is created in default handler
     * @covers \eZ\Publish\Core\IO\Handler\Dispatcher::__construct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testDispatcherInvalidAlternativeHandler()
    {
        new Dispatcher(
            $this->defaultBackend,
            array(
                array(
                    'handler' => 555,
                    // match conditions:
                    'prefix' => 'var/test/',
                    'suffix' => '.gif,.jpg',
                    'contains' => 'image-versioned'
                )
            )
        );
    }

    /**
     * Test that file is created in default handler
     * @covers \eZ\Publish\Core\IO\Handler\Dispatcher::__construct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testDispatcherInvalidAlternativeHandlerConfig()
    {
        new Dispatcher(
            $this->defaultBackend,
            array(
                array(
                    'handler' => $this->alternativeBackend,
                )
            )
        );
    }

    /**
     * Test that file is created in default handler
     * @covers \eZ\Publish\Core\IO\Handler\Dispatcher::getHandler
     */
    public function testDispatcherDefaultBackendCreate()
    {
        $repositoryPath = 'var/test/storage/images/ezplogo.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->IOHandler->create( $struct );
        $binaryFile2 = $this->defaultBackend->load( $repositoryPath );

        self::assertEquals( $binaryFile, $binaryFile2 );
    }

    /**
     * Test that file is created in default handler
     * @covers \eZ\Publish\Core\IO\Handler\Dispatcher::getHandler
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDispatcherDefaultBackendCreateNotFound()
    {
        $repositoryPath = 'var/test/storage/images/ezplogo.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->IOHandler->create( $struct );
        $this->alternativeBackend->load( $repositoryPath );
    }

    /**
     * Test that file is created in alternative handler
     * @covers \eZ\Publish\Core\IO\Handler\Dispatcher::getHandler
     */
    public function testDispatcherAlternativeBackendCreate()
    {
        $repositoryPath = 'var/test/storage/image-versioned/ezplogo.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->IOHandler->create( $struct );
        $binaryFile2 = $this->alternativeBackend->load( $repositoryPath );

        self::assertEquals( $binaryFile, $binaryFile2 );
    }

    /**
     * Test that file is created in alternative handler
     * @covers \eZ\Publish\Core\IO\Handler\Dispatcher::getHandler
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDispatcherAlternativeBackendCreateNotFound()
    {
        $repositoryPath = 'var/test/storage/image-versioned/ezplogo.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->IOHandler->create( $struct );
        $this->defaultBackend->load( $repositoryPath );
    }

    public function testGetMetadata( BinaryFile $binaryFile )
    {
        self::markTestIncomplete( "Not implemented" );
    }
}
