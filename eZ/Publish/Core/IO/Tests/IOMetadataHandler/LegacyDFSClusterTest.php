<?php

/**
 * This file is part of the eZ Publish Legacy package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Publish\Core\IO\Tests\IOMetadataHandler;

use eZ\Publish\Core\IO\IOMetadataHandler\LegacyDFSCluster;
use eZ\Publish\SPI\IO\BinaryFile as SPIBinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct as SPIBinaryFileCreateStruct;
use PHPUnit\Framework\TestCase;
use DateTime;

class LegacyDFSClusterTest extends TestCase
{
    /** @var \eZ\Publish\Core\IO\IOMetadataHandler|\PHPUnit_Framework_MockObject_MockObject */
    private $handler;

    /** @var \Doctrine\DBAL\Connection|\PHPUnit_Framework_MockObject_MockObject */
    private $dbalMock;

    /** @var \eZ\Publish\Core\IO\UrlDecorator|\PHPUnit_Framework_MockObject_MockObject */
    private $urlDecoratorMock;

    public function setUp()
    {
        $this->dbalMock = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
        $this->urlDecoratorMock = $this->getMock('eZ\Publish\Core\IO\UrlDecorator');

        $this->handler = new LegacyDFSCluster(
            $this->dbalMock,
            $this->urlDecoratorMock,
            ['prefix' => 'var/test']
        );
    }

    public function providerCreate()
    {
        return [
            ['prefix/my/file.png', 'image/png', 123, new DateTime('@1307155200'), new DateTime('@1307155200')],
            ['prefix/my/file.png', 'image/png', 123, new DateTime('@1307155200'), new DateTime('@1307155200')], // Duplicate, should not fail
            ['prefix/my/file.png', 'image/png', 123, new DateTime('@1307155242'), new DateTime('@1307155242')],
        ];
    }

    /**
     * @dataProvider providerCreate
     */
    public function testCreate($id, $mimeType, $size, $mtime, $mtimeExpected)
    {
        $this->dbalMock
            ->expects($this->once())
            ->method('prepare')
            ->with($this->anything())
            ->will($this->returnValue($this->createDbalStatementMock()));

        $spiCreateStruct = new SPIBinaryFileCreateStruct();
        $spiCreateStruct->id = $id;
        $spiCreateStruct->mimeType = $mimeType;
        $spiCreateStruct->size = $size;
        $spiCreateStruct->mtime = $mtime;

        $spiBinary = $this->handler->create($spiCreateStruct);

        $this->assertInstanceOf('eZ\Publish\SPI\IO\BinaryFile', $spiBinary);

        $this->assertEquals($mtimeExpected, $spiBinary->mtime);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateInvalidArgument()
    {
        $this->dbalMock
            ->expects($this->never())
            ->method('prepare');

        $spiCreateStruct = new SPIBinaryFileCreateStruct();
        $spiCreateStruct->id = 'prefix/my/file.png';
        $spiCreateStruct->mimeType = 'image/png';
        $spiCreateStruct->size = 123;
        $spiCreateStruct->mtime = 1307155242; // Invalid, should be a DateTime

        $this->handler->create($spiCreateStruct);
    }

    public function testDelete()
    {
        $statement = $this->createDbalStatementMock();
        $statement
            ->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));

        $this->dbalMock
            ->expects($this->once())
            ->method('prepare')
            ->with($this->anything())
            ->will($this->returnValue($statement));

        $this->handler->delete('prefix/my/file.png');
    }

    /**
     * @expectedException \eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException
     */
    public function testDeleteNotFound()
    {
        $statement = $this->createDbalStatementMock();
        $statement
            ->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(0));

        $this->dbalMock
            ->expects($this->once())
            ->method('prepare')
            ->with($this->anything())
            ->will($this->returnValue($statement));

        $this->handler->delete('prefix/my/file.png');
    }

    public function testLoad()
    {
        $statement = $this->createDbalStatementMock();
        $statement
            ->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));

        $statement
            ->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(array('size' => 123, 'datatype' => 'image/png', 'mtime' => 1307155200)));

        $this->dbalMock
            ->expects($this->once())
            ->method('prepare')
            ->with($this->anything())
            ->will($this->returnValue($statement));

        $expectedSpiBinaryFile = new SPIBinaryFile();
        $expectedSpiBinaryFile->id = 'prefix/my/file.png';
        $expectedSpiBinaryFile->size = 123;
        $expectedSpiBinaryFile->mtime = new DateTime('@1307155200');
        $expectedSpiBinaryFile->mimeType = 'image/png';

        self::assertEquals(
            $expectedSpiBinaryFile,
            $this->handler->load('prefix/my/file.png')
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException
     */
    public function testLoadNotFound()
    {
        $statement = $this->createDbalStatementMock();
        $statement
            ->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(0));

        $this->dbalMock
            ->expects($this->once())
            ->method('prepare')
            ->with($this->anything())
            ->will($this->returnValue($statement));

        $this->handler->load('prefix/my/file.png');
    }

    public function testExists()
    {
        $statement = $this->createDbalStatementMock();
        $statement
            ->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));

        $this->dbalMock
            ->expects($this->once())
            ->method('prepare')
            ->with($this->anything())
            ->will($this->returnValue($statement));

        self::assertTrue($this->handler->exists('prefix/my/file.png'));
    }

    public function testExistsNot()
    {
        $statement = $this->createDbalStatementMock();
        $statement
            ->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(0));

        $this->dbalMock
            ->expects($this->once())
            ->method('prepare')
            ->with($this->anything())
            ->will($this->returnValue($statement));

        self::assertFalse($this->handler->exists('prefix/my/file.png'));
    }

    public function testDeletedirectory()
    {
        $statement = $this->createDbalStatementMock();
        $statement
            ->expects($this->once())
            ->method('bindValue')
            ->with(1, 'folder/subfolder/%');

        $this->dbalMock
            ->expects($this->once())
            ->method('prepare')
            ->with($this->anything())
            ->will($this->returnValue($statement));

        $this->handler->deleteDirectory('folder/subfolder/');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createDbalStatementMock()
    {
        return $this->getMock('Doctrine\DBAL\Driver\Statement');
    }
}
