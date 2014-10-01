<?php
/**
 * File containing a Io Handler test
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Tests\Handler;

use eZ\Publish\Core\IO\Handler\Dispatcher;
use eZ\Publish\Core\IO\Handler\Filesystem as FilesystemHandler;
use eZ\Publish\Core\IO\MimeTypeDetector\FileInfo as FileInfoMimeTypeDetector;
use eZ\Publish\Core\IO\Tests\Handler\FilesystemTest;
use eZ\Publish\SPI\IO\BinaryFile;

/**
 * Handler test
 */
class DispatcherTest extends FilesystemTest
{
    /**
     * @var \eZ\Publish\SPI\IO\Handler
     */
    protected $defaultBackend;

    /**
     * @var \eZ\Publish\SPI\IO\Handler
     */
    protected $alternativeBackend;

    /**
     * @return \eZ\Publish\Core\IO\Handler
     */
    protected function getIOHandler( $path = null )
    {
        $path = $path ?: self::$storageDir;
        if ( !file_exists( $path . '/default' ) )
            @mkdir( $path . '/default' );

        if ( !file_exists( $path . '/alternative' ) )
            @mkdir( $path . '/alternative' );

        $this->defaultBackend = new FilesystemHandler( new FileInfoMimeTypeDetector(), $path . '/default' );
        $this->alternativeBackend = new FilesystemHandler( new FileInfoMimeTypeDetector(), $path . '/alternative'  );
        return new Dispatcher(
            $this->defaultBackend,
            array(
                array(
                    'handler' => $this->alternativeBackend,
                    // match conditions, simulating alternative handler for draft images, for more effective matching
                    // it should have some form of 'at' condition to match (===) on a specific path element
                    'prefix' => $path . '/',
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
                    'prefix' => self::$storageDir . '/',
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
        $repositoryPath = self::$storageDir . '/storage/images/ezplogo.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->IOHandler->create( $struct );
        $binaryFile2 = $this->defaultBackend->load( $repositoryPath );

        self::assertEquals( $binaryFile, $binaryFile2 );
    }

    /**
     * Test that file is created in default handler
     *
     * @covers \eZ\Publish\Core\IO\Handler\Dispatcher::getHandler
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDispatcherDefaultBackendCreateNotFound()
    {
        $repositoryPath = self::$storageDir . '/storage/images/ezplogo.gif';
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
        $repositoryPath = self::$storageDir . '/storage/image-versioned/ezplogo.gif';
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
        $repositoryPath = self::$storageDir . '/storage/image-versioned/ezplogo.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->IOHandler->create( $struct );
        $this->defaultBackend->load( $repositoryPath );
    }
}
