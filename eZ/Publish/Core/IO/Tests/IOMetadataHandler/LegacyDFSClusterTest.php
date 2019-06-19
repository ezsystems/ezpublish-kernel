<?php

/**
 * This file is part of the eZ Publish Legacy package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Publish\Core\IO\Tests\IOMetadataHandler;

use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\IO\IOMetadataHandler\LegacyDFSCluster;
use eZ\Publish\SPI\IO\BinaryFile as SPIBinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct as SPIBinaryFileCreateStruct;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use eZ\Publish\Core\IO\UrlDecorator;
use PHPUnit\Framework\TestCase;
use DateTime;

class LegacyDFSClusterTest extends TestCase
{
    /** @var \eZ\Publish\Core\IO\IOMetadataHandler|\PHPUnit\Framework\MockObject\MockObject */
    private $handler;

    /** @var \Doctrine\DBAL\Connection|\PHPUnit\Framework\MockObject\MockObject */
    private $dbalMock;

    /** @var \eZ\Publish\Core\IO\UrlDecorator|\PHPUnit\Framework\MockObject\MockObject */
    private $urlDecoratorMock;

    public function setUp()
    {
        $this->dbalMock = $this->createMock(Connection::class);
        $this->urlDecoratorMock = $this->createMock(UrlDecorator::class);

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

        $this->assertInstanceOf(SPIBinaryFile::class, $spiBinary);

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
            ->will($this->returnValue(['size' => 123, 'datatype' => 'image/png', 'mtime' => 1307155200]));

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
        $this->urlDecoratorMock
            ->expects($this->once())
            ->method('decorate')
            ->will($this->returnValue('prefix/images/_alias/subfolder'));

        $queryBuilderMock = $this
            ->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilderMock->expects($this->at(0))
            ->method('delete')
            ->with('ezdfsfile')
            ->willReturn($queryBuilderMock);

        $queryBuilderMock->expects($this->at(1))
            ->method('where')
            ->with('name LIKE :spiPath ESCAPE :esc')
            ->willReturn($queryBuilderMock);

        $queryBuilderMock->expects($this->at(2))
            ->method('setParameter')
            ->with(':esc', '\\')
            ->willReturn($queryBuilderMock);

        $queryBuilderMock->expects($this->at(3))
            ->method('setParameter')
            ->with(':spiPath', 'prefix/images/\_alias/subfolder/%')
            ->willReturn($queryBuilderMock);

        $queryBuilderMock->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $this->dbalMock
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        $this->handler->deleteDirectory('images/_alias/subfolder/');
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createDbalStatementMock()
    {
        return $this->createMock(Statement::class);
    }
}
