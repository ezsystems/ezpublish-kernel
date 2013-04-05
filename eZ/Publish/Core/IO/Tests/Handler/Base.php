<?php
/**
 * File containing the eZ\Publish\Core\IO\Tests\Base class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Tests\Handler;

use eZ\Publish\SPI\IO\BinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFileUpdateStruct;
use eZ\Publish\Core\IO\Handler as IOHandler;
use DateTime;
use finfo;

abstract class Base extends \PHPUnit_Framework_TestCase
{
    /**
     * Binary IoHandler
     * @var \eZ\Publish\Core\IO\Handler
     */
    protected $IOHandler;

    /**
     * Test image file
     * @var string
     */
    protected $imageInputPath;

    /**
     * Setup test
     */
    public function setUp()
    {
        parent::setUp();
        $this->IOHandler = $this->getIOHandler();
        $this->imageInputPath = __DIR__ . DIRECTORY_SEPARATOR . 'ezplogo.gif';
    }

    /**
     * @return \eZ\Publish\SPI\IO\Handler
     */
    abstract protected function getIOHandler();

    /**
     * Tear down test
     */
    protected function tearDown()
    {
        unset( $this->IOHandler );
        unset( $this->imageInputPath );
        parent::tearDown();
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::create
     */
    public function testCreate()
    {
        $repositoryPath = 'images/ezplogo.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->IOHandler->create( $struct );

        self::assertInstanceOf( 'eZ\\Publish\\SPI\\IO\\BinaryFile', $binaryFile );
        self::assertEquals( $repositoryPath, $binaryFile->uri );
        self::assertEquals( 1928, $binaryFile->size );
        self::assertInstanceOf( 'DateTime', $binaryFile->mtime );
        self::assertNotEquals( 0, $binaryFile->mtime->getTimestamp() );

        return $binaryFile;
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::create
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreatePathExists()
    {
        $repositoryPath = 'images/testCreateFileExists.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );

        $this->IOHandler->create( $struct );
        $this->IOHandler->create( $struct );
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::update
     */
    public function testUpdate()
    {
        $this->urlFopenPrecheck();
        $firstPath = 'images/update-before.gif';
        $secondPath = 'images/update-after.png';

        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $firstPath );
        $binaryFile = $this->IOHandler->create( $struct );

        self::assertTrue( $this->IOHandler->exists( $firstPath ) );
        self::assertFalse( $this->IOHandler->exists( $secondPath ) );
        self::assertEquals(
            md5_file( $this->imageInputPath ),
            md5( fread( $this->IOHandler->getFileResource( $firstPath ), $binaryFile->size ) )
        );

        $newFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'ezplogo2.png';
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->uri = $secondPath;
        $updateStruct->setInputStream( fopen( $newFilePath, 'rb' ) );
        $updateStruct->size = filesize( $newFilePath );

        $updatedFile = $this->IOHandler->update( $firstPath, $updateStruct );

        self::assertFalse( $this->IOHandler->exists( $firstPath ), "$firstPath should not exist" );
        self::assertTrue( $this->IOHandler->exists( $secondPath ), "$secondPath should exist" );
        self::assertEquals( $updateStruct->size, $updatedFile->size );
        self::assertEquals(
            md5_file( $newFilePath ),
            md5( fread( $this->IOHandler->getFileResource( $secondPath ), $updatedFile->size ) )
        );
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::update
     */
    public function testUpdateMtime()
    {
        $path = 'images/update-mtime.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $path );
        $binaryFile = $this->IOHandler->create( $struct );

        $newMtime = new DateTime( 'last week' );
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->mtime = $newMtime;

        $updatedBinaryFile = $this->IOHandler->update( $path, $updateStruct );
        self::assertEquals( $binaryFile->uri, $updatedBinaryFile->uri );
        self::assertEquals( $binaryFile->size, $updatedBinaryFile->size );

        self::assertEquals( $newMtime, $updatedBinaryFile->mtime );
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::update
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testUpdateNonExistingSource()
    {
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->uri = 'images/testUpdateSourceNotFoundTarget.png';

        $this->IOHandler->update( 'images/testUpdateSourceNotFoundSource.png', $updateStruct );
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::update
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateTargetExists()
    {
        $firstPath = 'images/testUpdateTargetExists-1.gif';
        $secondPath = 'images/testUpdateTargetExists-2.png';

        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $firstPath );
        $binaryFile = $this->IOHandler->create( $struct );

        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $secondPath );
        $binaryFile = $this->IOHandler->create( $struct );

        self::assertTrue( $this->IOHandler->exists( $firstPath ) );
        self::assertTrue( $this->IOHandler->exists( $secondPath ) );

        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->uri = $secondPath;

        $this->IOHandler->update( $firstPath, $updateStruct );
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::exists
     */
    public function testExists()
    {
        $repositoryPath = 'images/exists.gif';

        self::assertFalse( $this->IOHandler->exists( $repositoryPath ) );

        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->IOHandler->create( $struct );

        self::assertTrue( $this->IOHandler->exists( $repositoryPath ) );
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::delete
     */
    public function testDelete()
    {
        $repositoryPath = 'images/delete.gif';

        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->IOHandler->create( $struct );

        self::assertTrue( $this->IOHandler->exists( $repositoryPath ) );

        $this->IOHandler->delete( $repositoryPath );

        self::assertFalse( $this->IOHandler->exists( $repositoryPath ) );
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::delete
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteNonExistingFile()
    {
        $this->IOHandler->delete( 'images/deleteNonExisting.gif' );
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::load
     */
    public function testLoad()
    {
        $repositoryPath = 'images/load.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->IOHandler->create( $struct );

        $loadedFile = $this->IOHandler->load( $repositoryPath );

        self::assertInstanceOf( 'eZ\\Publish\\SPI\\IO\\BinaryFile', $loadedFile );

        self::assertEquals( 'images/load.gif', $loadedFile->uri );
        self::assertEquals( 1928, $loadedFile->size );
        self::assertInstanceOf( 'DateTime', $loadedFile->mtime );
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::load
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadNonExistingFile()
    {
        $this->IOHandler->load( 'images/loadNotFound.png' );
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::getFileResource
     */
    public function testGetFileResource()
    {
        $this->urlFopenPrecheck();
        $repositoryPath = 'images/getfileresource.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->IOHandler->create( $struct );

        $resource = $this->IOHandler->getFileResource( $repositoryPath );

        $storedDataSum = md5( fread( $resource, $binaryFile->size ) );
        $originalDataSum = md5( file_get_contents( $this->imageInputPath ) );
        self::assertEquals( $originalDataSum, $storedDataSum );
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::getFileResource
     * @expectedException eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testGetFileResourceNonExistingFile()
    {
        $this->IOHandler->getFileResource( 'images/testGetFileResourceNonExistingFile.png' );
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::getFileContents
     */
    public function testGetFileContents()
    {
        $repositoryPath = 'images/testGetFileContents.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $this->IOHandler->create( $struct );

        self::assertEquals( file_get_contents( $this->imageInputPath ), $this->IOHandler->getFileContents( $repositoryPath ) );
    }

    /**
     * @covers \eZ\Publish\Core\IO\Handler::getFileContents
     * @expectedException eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testGetFileContentsNonExistingFile()
    {
        $this->IOHandler->getFileContents( 'testGetFileContentsNonExistingFile.gif' );
    }

    /**
     * @depends testCreate
     * @param BinaryFile $binaryFile
     */
    public function testGetMetadata( BinaryFile $binaryFile )
    {
        // @todo Add @depends on createFile
        $path = $binaryFile->uri;
        $internalPath = $this->IOHandler->getInternalPath( $path );

        $metadataHandlerMock = $this->getMock( 'eZ\\Publish\\Core\\IO\\MetadataHandler' );
        $expectedMetadata = array( 'some' => 1, 'meta' => 2 );
        $metadataHandlerMock
            ->expects( $this->once() )
            ->method( 'extract' )
            ->will( $this->returnValue( $expectedMetadata ) );

        $metadata = $this->IOHandler->getMetadata(
            $metadataHandlerMock,
            $path
        );

        self::assertEquals( $metadata, $expectedMetadata );
    }

        /**
     * Creates a BinaryFile object from $localFile
     *
     * @throws \Exception When given a non existing / unreadable file
     * @param string $localFile Path to local file
     * @param string $repositoryPath The path the file must be stored as
     *
     * @return \eZ\Publish\SPI\IO\BinaryFileCreateStruct
     */
    protected function getCreateStructFromLocalFile( $localFile, $repositoryPath )
    {
        if ( !file_exists( $localFile ) || !is_readable( $localFile ) )
        {
            {
                throw new \Exception( "Could not find/read file: {$localFile}" );
            }
        }

        $struct = new BinaryFileCreateStruct();
        $struct->size = filesize( $localFile );
        $struct->uri = $repositoryPath;
        $struct->mimeType = $this->getMimeTypeFromPath( $localFile );

        $inputStream = fopen( $localFile, 'rb' );
        $struct->setInputStream( $inputStream );

        return $struct;
    }

    /**
     * Returns a mimeType from a file path, using fileinfo
     *
     * @throws \Exception If file does not exist
     * @param string $path
     *
     * @return string
     */
    protected static function getMimeTypeFromPath( $path )
    {
        if ( !file_exists( $path ) )
        {
            throw new \Exception( "Could not fine file: {$path}" );
        }

        $finfo = new finfo( FILEINFO_MIME_TYPE );
        return $finfo->file( $path );
    }

    /**
     * Reusable function for tests that needs allow_url_fopen, skip if disabled
     */
    protected function urlFopenPrecheck()
    {
        if ( ini_get( "allow_url_fopen" ) != 1 )
            $this->markTestSkipped( "allow_url_fopen must be 'On' for this test." );
    }
}
