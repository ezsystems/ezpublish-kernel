<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\Tests\IOMetadataHandler;

use eZ\Publish\Core\IO\IOMetadataHandler\Flysystem;
use eZ\Publish\SPI\IO\BinaryFile as SPIBinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct as SPIBinaryFileCreateStruct;
use League\Flysystem\FileNotFoundException;
use PHPUnit_Framework_TestCase;
use DateTime;

class FlysystemTest extends PHPUnit_Framework_TestCase
{
    /** @var \eZ\Publish\Core\IO\IOMetadataHandler|\PHPUnit_Framework_MockObject_MockObject */
    private $handler;

    /** @var \League\Flysystem\FilesystemInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $filesystem;

    public function setUp()
    {
        $this->filesystem = $this->getMock( 'League\Flysystem\FilesystemInterface' );
        $this->handler = new Flysystem( $this->filesystem );
    }

    public function testCreate()
    {
        // good example of bad responsibilities... since create also loads, we test the same thing twice
        $spiCreateStruct = new SPIBinaryFileCreateStruct();
        $spiCreateStruct->id = 'prefix/my/file.png';
        $spiCreateStruct->size = 123;
        $spiCreateStruct->mtime = new DateTime( '@1307155200' );

        $expectedSpiBinaryFile = new SPIBinaryFile();
        $expectedSpiBinaryFile->id = 'prefix/my/file.png';
        $expectedSpiBinaryFile->size = 123;
        $expectedSpiBinaryFile->mtime = new DateTime( '@1307155200' );

        $this->filesystem
            ->expects( $this->once() )
            ->method( 'getMetadata' )
            ->with( $spiCreateStruct->id )
            ->will(
                $this->returnValue(
                    array(
                        'timestamp' => 1307155200,
                        'size' => 123
                    )
                )
            );

        $spiBinaryFile = $this->handler->create( $spiCreateStruct );

        $this->assertInstanceOf( 'eZ\Publish\SPI\IO\BinaryFile', $spiBinaryFile );
        $this->assertEquals( $expectedSpiBinaryFile, $spiBinaryFile );
    }

    public function testDelete()
    {
        $this->handler->delete( 'prefix/my/file.png' );
    }

    public function testLoad()
    {
        $expectedSpiBinaryFile = new SPIBinaryFile();
        $expectedSpiBinaryFile->id = 'prefix/my/file.png';
        $expectedSpiBinaryFile->size = 123;
        $expectedSpiBinaryFile->mtime = new DateTime( '@1307155200' );

        $this->filesystem
            ->expects( $this->once() )
            ->method( 'getMetadata' )
            ->with( 'prefix/my/file.png' )
            ->will(
                $this->returnValue(
                    array(
                        'timestamp' => 1307155200,
                        'size' => 123,
                    )
                )
            );

        $spiBinaryFile = $this->handler->load( 'prefix/my/file.png' );

        $this->assertInstanceOf( 'eZ\Publish\SPI\IO\BinaryFile', $spiBinaryFile );
        $this->assertEquals( $expectedSpiBinaryFile, $spiBinaryFile );
    }

    /**
     * @expectedException \eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException
     */
    public function testLoadNotFound()
    {
        $this->filesystem
            ->expects( $this->once() )
            ->method( 'getMetadata' )
            ->with( 'prefix/my/file.png' )
            ->will( $this->throwException( new FileNotFoundException( 'prefix/my/file.png' ) ) );

        $this->handler->load( 'prefix/my/file.png' );
    }

    public function testExists()
    {
        $this->filesystem
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'prefix/my/file.png' )
            ->will( $this->returnValue( true ) );

        self::assertTrue( $this->handler->exists( 'prefix/my/file.png' ) );
    }

    public function testExistsNot()
    {
        $this->filesystem
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'prefix/my/file.png' )
            ->will( $this->returnValue( false ) );

        self::assertFalse( $this->handler->exists( 'prefix/my/file.png' ) );
    }

    public function testGetMimeType()
    {
        $this->filesystem
            ->expects( $this->once() )
            ->method( 'getMimeType' )
            ->with( 'file.txt' )
            ->will( $this->returnValue( 'text/plain' ) );

        self::assertEquals( 'text/plain', $this->handler->getMimeType( 'file.txt' ) );
    }
}
