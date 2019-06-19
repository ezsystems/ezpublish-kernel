<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\Tests\IOMetadataHandler;

use eZ\Publish\Core\IO\IOMetadataHandler\Flysystem;
use eZ\Publish\SPI\IO\BinaryFile as SPIBinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct as SPIBinaryFileCreateStruct;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\TestCase;
use DateTime;

class FlysystemTest extends TestCase
{
    /** @var \eZ\Publish\Core\IO\IOMetadataHandler|\PHPUnit\Framework\MockObject\MockObject */
    private $handler;

    /** @var \League\Flysystem\FilesystemInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $filesystem;

    public function setUp()
    {
        $this->filesystem = $this->createMock(FilesystemInterface::class);
        $this->handler = new Flysystem($this->filesystem);
    }

    public function testCreate()
    {
        // good example of bad responsibilities... since create also loads, we test the same thing twice
        $spiCreateStruct = new SPIBinaryFileCreateStruct();
        $spiCreateStruct->id = 'prefix/my/file.png';
        $spiCreateStruct->size = 123;
        $spiCreateStruct->mtime = new DateTime('@1307155200');

        $expectedSpiBinaryFile = new SPIBinaryFile();
        $expectedSpiBinaryFile->id = 'prefix/my/file.png';
        $expectedSpiBinaryFile->size = 123;
        $expectedSpiBinaryFile->mtime = new DateTime('@1307155200');

        $this->filesystem
            ->expects($this->once())
            ->method('getMetadata')
            ->with($spiCreateStruct->id)
            ->will(
                $this->returnValue(
                    [
                        'timestamp' => 1307155200,
                        'size' => 123,
                    ]
                )
            );

        $spiBinaryFile = $this->handler->create($spiCreateStruct);

        $this->assertInstanceOf(SPIBinaryFile::class, $spiBinaryFile);
        $this->assertEquals($expectedSpiBinaryFile, $spiBinaryFile);
    }

    public function testDelete()
    {
        $this->filesystem->expects($this->never())->method('delete');
        $this->handler->delete('prefix/my/file.png');
    }

    public function testLoad()
    {
        $expectedSpiBinaryFile = new SPIBinaryFile();
        $expectedSpiBinaryFile->id = 'prefix/my/file.png';
        $expectedSpiBinaryFile->size = 123;
        $expectedSpiBinaryFile->mtime = new DateTime('@1307155200');

        $this->filesystem
            ->expects($this->once())
            ->method('getMetadata')
            ->with('prefix/my/file.png')
            ->will(
                $this->returnValue(
                    [
                        'timestamp' => 1307155200,
                        'size' => 123,
                    ]
                )
            );

        $spiBinaryFile = $this->handler->load('prefix/my/file.png');

        $this->assertInstanceOf(SPIBinaryFile::class, $spiBinaryFile);
        $this->assertEquals($expectedSpiBinaryFile, $spiBinaryFile);
    }

    /**
     * The timestamp index can be unset with some handlers, like AWS/S3.
     */
    public function testLoadNoTimestamp()
    {
        $this->filesystem
            ->expects($this->once())
            ->method('getMetadata')
            ->with('prefix/my/file.png')
            ->will(
                $this->returnValue(
                    [
                        'size' => 123,
                    ]
                )
            );

        $spiBinaryFile = $this->handler->load('prefix/my/file.png');
        $this->assertNull($spiBinaryFile->mtime);
    }

    /**
     * @expectedException \eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException
     */
    public function testLoadNotFound()
    {
        $this->filesystem
            ->expects($this->once())
            ->method('getMetadata')
            ->with('prefix/my/file.png')
            ->will($this->throwException(new FileNotFoundException('prefix/my/file.png')));

        $this->handler->load('prefix/my/file.png');
    }

    public function testExists()
    {
        $this->filesystem
            ->expects($this->once())
            ->method('has')
            ->with('prefix/my/file.png')
            ->will($this->returnValue(true));

        self::assertTrue($this->handler->exists('prefix/my/file.png'));
    }

    public function testExistsNot()
    {
        $this->filesystem
            ->expects($this->once())
            ->method('has')
            ->with('prefix/my/file.png')
            ->will($this->returnValue(false));

        self::assertFalse($this->handler->exists('prefix/my/file.png'));
    }

    public function testGetMimeType()
    {
        $this->filesystem
            ->expects($this->once())
            ->method('getMimeType')
            ->with('file.txt')
            ->will($this->returnValue('text/plain'));

        self::assertEquals('text/plain', $this->handler->getMimeType('file.txt'));
    }

    public function testDeleteDirectory()
    {
        $this->filesystem->expects($this->never())->method('deleteDir');
        $this->handler->deleteDirectory('some/path');
    }
}
