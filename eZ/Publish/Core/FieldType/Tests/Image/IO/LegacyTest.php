<?php

/**
 * File containing the LegacyTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Tests\Image\IO;

use eZ\Publish\Core\FieldType\Image\IO\Legacy as LegacyIOService;
use eZ\Publish\Core\FieldType\Image\IO\OptionsProvider;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class LegacyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\FieldType\Image\IO\Legacy
     */
    protected $service;

    /**
     * Internal IOService instance for published images.
     *
     * @var \eZ\Publish\Core\IO\IOServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $publishedIoServiceMock;

    /**
     * Internal IOService instance for draft images.
     *
     * @var \eZ\Publish\Core\IO\IOServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $draftIoServiceMock;

    public function setUp()
    {
        $this->publishedIoServiceMock = $this->getMock('eZ\Publish\Core\IO\IOServiceInterface');
        $this->draftIoServiceMock = $this->getMock('eZ\Publish\Core\IO\IOServiceInterface');
        $optionsProvider = new OptionsProvider(
            array(
                'var_dir' => 'var/test',
                'storage_dir' => 'storage',
                'draft_images_dir' => 'images-versioned',
                'published_images_dir' => 'images',
            )
        );
        $this->service = new LegacyIOService(
            $this->publishedIoServiceMock,
            $this->draftIoServiceMock,
            $optionsProvider
        );
    }

    public function testGetExternalPath()
    {
        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('getExternalPath')
            ->with('var/test/storage/images/path/file.png')
            ->will($this->returnValue('path/file.png'));

        self::assertEquals(
            'path/file.png',
            $this->service->getExternalPath('var/test/storage/images/path/file.png')
        );
    }

    public function testNewBinaryCreateStructFromLocalFile()
    {
        $path = '/tmp/file.png';
        $struct = new BinaryFileCreateStruct();
        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('newBinaryCreateStructFromLocalFile')
            ->with($path)
            ->will($this->returnValue($struct));

        $this->draftIoServiceMock->expects($this->never())->method('newBinaryCreateStructFromLocalFile');

        self::assertEquals(
            $struct,
            $this->service->newBinaryCreateStructFromLocalFile($path)
        );
    }

    public function testExists()
    {
        $path = 'path/file.png';
        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('exists')
            ->with($path)
            ->will($this->returnValue(true));

        $this->draftIoServiceMock->expects($this->never())->method('exists');

        self::assertTrue(
            $this->service->exists($path)
        );
    }

    public function testGetInternalPath()
    {
        $id = 'path/file.png';
        $internalPath = 'var/test/storage/images/path/file.png';

        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('getInternalPath')
            ->with($id)
            ->will($this->returnValue($internalPath));

        $this->draftIoServiceMock->expects($this->never())->method('getInternalPath');

        self::assertEquals(
            $internalPath,
            $this->service->getInternalPath($id)
        );
    }

    /**
     * Standard binary file, with regular id.
     */
    public function testLoadBinaryFile()
    {
        $id = 'path/file.jpg';
        $binaryFile = new BinaryFile(array('id' => $id));

        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('loadBinaryFile')
            ->with($id)
            ->will($this->returnValue($binaryFile));

        $this->draftIoServiceMock->expects($this->never())->method('loadBinaryFile');

        self::assertSame(
            $binaryFile,
            $this->service->loadBinaryFile($id)
        );
    }

    /**
     * Load from internal draft binary file path.
     */
    public function testLoadBinaryFileDraftInternalPath()
    {
        $internalId = 'var/test/storage/images-versioned/path/file.jpg';
        $id = 'path/file.jpg';
        $binaryFile = new BinaryFile(array('id' => $id));

        $this->draftIoServiceMock
            ->expects($this->once())
            ->method('getExternalPath')
            ->with($internalId)
            ->will($this->returnValue($id));

        $this->draftIoServiceMock
            ->expects($this->once())
            ->method('loadBinaryFile')
            ->with($id)
            ->will($this->returnValue($binaryFile));

        $this->publishedIoServiceMock->expects($this->never())->method('loadBinaryFile');

        self::assertSame(
            $binaryFile,
            $this->service->loadBinaryFile($internalId)
        );
    }

    /**
     * Load from internal published binary file path.
     */
    public function testLoadBinaryFilePublishedInternalPath()
    {
        $internalId = 'var/test/storage/images/path/file.jpg';
        $id = 'path/file.jpg';
        $binaryFile = new BinaryFile(array('id' => $id));

        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('getExternalPath')
            ->with($internalId)
            ->will($this->returnValue($id));

        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('loadBinaryFile')
            ->with($id)
            ->will($this->returnValue($binaryFile));

        $this->draftIoServiceMock->expects($this->never())->method('loadBinaryFile');

        self::assertSame(
            $binaryFile,
            $this->service->loadBinaryFile($internalId)
        );
    }

    public function testLoadBinaryFileByUriWithPublishedFile()
    {
        $binaryFileUri = 'var/test/images/an/image.png';
        $binaryFile = new BinaryFile(array('id' => 'an/image.png'));
        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('loadBinaryFileByUri')
            ->with($binaryFileUri)
            ->will($this->returnValue($binaryFile));

        self::assertSame(
            $binaryFile,
            $this->service->loadBinaryFileByUri($binaryFileUri)
        );
    }

    public function testLoadBinaryFileByUriWithDraftFile()
    {
        $binaryFileUri = 'var/test/images-versioned/an/image.png';
        $binaryFile = new BinaryFile(array('id' => 'an/image.png'));

        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('loadBinaryFileByUri')
            ->with($binaryFileUri)
            ->will($this->throwException(new InvalidArgumentException('$id', "Prefix not found in {$binaryFile->id}")));

        $this->draftIoServiceMock
            ->expects($this->once())
            ->method('loadBinaryFileByUri')
            ->with($binaryFileUri)
            ->will($this->returnValue($binaryFile));

        self::assertSame(
            $binaryFile,
            $this->service->loadBinaryFileByUri($binaryFileUri)
        );
    }

    public function testGetFileContents()
    {
        $binaryFile = new BinaryFile();
        $contents = 'some contents';

        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('getFileContents')
            ->with($binaryFile)
            ->will($this->returnValue($contents));

        $this->draftIoServiceMock->expects($this->never())->method('getFileContents');

        self::assertSame(
            $contents,
            $this->service->getFileContents($binaryFile)
        );
    }

    public function testCreateBinaryFile()
    {
        $createStruct = new BinaryFileCreateStruct();
        $binaryFile = new BinaryFile();

        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('createBinaryFile')
            ->with($createStruct)
            ->will($this->returnValue($binaryFile));

        $this->draftIoServiceMock->expects($this->never())->method('createBinaryFile');

        self::assertSame(
            $binaryFile,
            $this->service->createBinaryFile($createStruct)
        );
    }

    public function testGetUri()
    {
        $binaryFile = new BinaryFile();
        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('getUri')
            ->with($binaryFile)
            ->will($this->returnValue('protocol://uri'));

        $this->draftIoServiceMock->expects($this->never())->method('getUri');

        self::assertEquals(
            'protocol://uri',
            $this->service->getUri($binaryFile)
        );
    }

    public function testGetFileInputStream()
    {
        $binaryFile = new BinaryFile();
        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('getFileInputStream')
            ->with($binaryFile)
            ->will($this->returnValue('resource'));

        $this->draftIoServiceMock->expects($this->never())->method('getFileInputStream');

        self::assertEquals(
            'resource',
            $this->service->getFileInputStream($binaryFile)
        );
    }

    public function testDeleteBinaryFile()
    {
        $binaryFile = new BinaryFile();
        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('deleteBinaryFile')
            ->with($binaryFile);

        $this->draftIoServiceMock->expects($this->never())->method('deleteBinaryFile');

        $this->service->deleteBinaryFile($binaryFile);
    }

    public function testNewBinaryCreateStructFromUploadedFile()
    {
        $struct = new BinaryFileCreateStruct();
        $this->publishedIoServiceMock
            ->expects($this->once())
            ->method('newBinaryCreateStructFromUploadedFile')
            ->with(array())
            ->will($this->returnValue($struct));

        $this->draftIoServiceMock->expects($this->never())->method('newBinaryCreateStructFromUploadedFile');

        self::assertEquals(
            $struct,
            $this->service->newBinaryCreateStructFromUploadedFile(array())
        );
    }
}
