<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\Tests\IOBinarydataHandler;

use eZ\Publish\Core\IO\IOBinarydataHandler\Flysystem;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\TestCase;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct as SPIBinaryFileCreateStruct;

class FlysystemTest extends TestCase
{
    /** @var \eZ\Publish\Core\IO\IOBinarydataHandler|\PHPUnit\Framework\MockObject\MockObject */
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
        $stream = fopen('php://memory', 'rb');
        $spiBinaryFileCreateStruct = new SPIBinaryFileCreateStruct();
        $spiBinaryFileCreateStruct->id = 'prefix/my/file.png';
        $spiBinaryFileCreateStruct->mimeType = 'image/png';
        $spiBinaryFileCreateStruct->size = 123;
        $spiBinaryFileCreateStruct->mtime = 1307155200;
        $spiBinaryFileCreateStruct->setInputStream($stream);

        $this->filesystem
            ->expects($this->once())
            ->method('writeStream')
            ->with(
                $this->equalTo($spiBinaryFileCreateStruct->id),
                $this->equalTo($stream),
                $this->equalTo(['mimetype' => 'image/png', 'visibility' => 'public'])
            );

        $this->handler->create($spiBinaryFileCreateStruct);
    }

    public function testCreateOverwritesIfExists()
    {
        $stream = fopen('php://memory', 'rb');
        $spiBinaryFileCreateStruct = new SPIBinaryFileCreateStruct();
        $spiBinaryFileCreateStruct->id = 'prefix/my/file.png';
        $spiBinaryFileCreateStruct->mimeType = 'image/png';
        $spiBinaryFileCreateStruct->size = 123;
        $spiBinaryFileCreateStruct->mtime = 1307155200;
        $spiBinaryFileCreateStruct->setInputStream($stream);

        $this->filesystem
            ->expects($this->once())
            ->method('writeStream')
            ->with(
                $this->equalTo($spiBinaryFileCreateStruct->id),
                $this->equalTo($stream),
                $this->equalTo(['mimetype' => 'image/png', 'visibility' => 'public'])
            )
            ->will($this->throwException(new FileExistsException('prefix/my/file.png')));

        $this->filesystem
            ->expects($this->once())
            ->method('updateStream')
            ->with(
                $this->equalTo($spiBinaryFileCreateStruct->id),
                $this->equalTo($stream),
                $this->equalTo(['mimetype' => 'image/png', 'visibility' => 'public'])
            );

        $this->handler->create($spiBinaryFileCreateStruct);
    }

    public function testDelete()
    {
        $this->filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('prefix/my/file.png');

        $this->handler->delete('prefix/my/file.png');
    }

    /**
     * @expectedException \eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException
     */
    public function testDeleteNotFound()
    {
        $this->filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('prefix/my/file.png')
            ->will($this->throwException(new FileNotFoundException('prefix/my/file.png')));

        $this->handler->delete('prefix/my/file.png');
    }

    public function testGetContents()
    {
        $this->filesystem
            ->expects($this->once())
            ->method('read')
            ->with('prefix/my/file.png')
            ->will($this->returnValue('This is my contents'));

        self::assertEquals(
            'This is my contents',
            $this->handler->getContents('prefix/my/file.png')
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException
     */
    public function testGetContentsNotFound()
    {
        $this->filesystem
            ->expects($this->once())
            ->method('read')
            ->with('prefix/my/file.png')
            ->will($this->throwException(new FileNotFoundException('prefix/my/file.png')));

        self::assertEquals(
            'This is my contents',
            $this->handler->getContents('prefix/my/file.png')
        );
    }

    public function testGetResource()
    {
        $resource = fopen('php://temp', 'rb');

        $this->filesystem
            ->expects($this->once())
            ->method('readStream')
            ->with('prefix/my/file.png')
            ->will($this->returnValue($resource));

        self::assertEquals(
            $resource,
            $this->handler->getResource('prefix/my/file.png')
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException
     */
    public function testGetResourceNotFound()
    {
        $this->filesystem
            ->expects($this->once())
            ->method('readStream')
            ->with('prefix/my/file.png')
            ->will($this->throwException(new FileNotFoundException('prefix/my/file.png')));

        $this->handler->getResource('prefix/my/file.png');
    }

    public function testGetUri()
    {
        self::assertEquals(
            '/prefix/my/file.png',
            $this->handler->getUri('prefix/my/file.png')
        );
    }

    public function testDeleteDirectory()
    {
        $this->filesystem
            ->expects($this->once())
            ->method('deleteDir')
            ->with('some/path');

        $this->handler->deleteDirectory('some/path');
    }
}
