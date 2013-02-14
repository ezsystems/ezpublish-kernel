<?php
/**
 * File containing the eZ\Publish\Core\IO\Tests\Base class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Tests;

use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFileUpdateStruct;
use DateTime;
use finfo;

abstract class Base extends \PHPUnit_Framework_TestCase
{
    /**
     * Binary IoHandler
     * @var \eZ\Publish\SPI\IO\Handler
     */
    protected $ioHandler;

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
        $this->ioHandler = $this->getIoHandler();
        $this->imageInputPath = __DIR__ . DIRECTORY_SEPARATOR . 'ezplogo.gif';
    }

    /**
     * @return \eZ\Publish\SPI\IO\Handler
     */
    abstract protected function getIoHandler();

    /**
     * Tear down test
     */
    protected function tearDown()
    {
        unset( $this->ioHandler );
        unset( $this->imageInputPath );
        parent::tearDown();
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::create
     */
    public function testCreate()
    {
        $repositoryPath = 'var/test/storage/images/ezplogo.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->ioHandler->create( $struct );

        self::assertInstanceOf( 'eZ\\Publish\\SPI\\IO\\BinaryFile', $binaryFile );
        self::assertEquals( $repositoryPath, $binaryFile->path );
        self::assertEquals( 1928, $binaryFile->size );
        self::assertInstanceOf( 'DateTime', $binaryFile->mtime );
        self::assertNotEquals( 0, $binaryFile->mtime->getTimestamp() );
        self::assertEquals( 'image/gif', $binaryFile->mimeType );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::create
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreatePathExists()
    {
        $repositoryPath = 'var/test/storage/images/testCreateFileExists.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );

        $binaryFile = $this->ioHandler->create( $struct );
        $binaryFile = $this->ioHandler->create( $struct );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::update
     */
    public function testUpdate()
    {
        $this->urlFopenPrecheck();
        $firstPath = 'var/test/update-before.gif';
        $secondPath = 'var/test/update-after.png';

        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $firstPath );
        $binaryFile = $this->ioHandler->create( $struct );

        self::assertTrue( $this->ioHandler->exists( $firstPath ) );
        self::assertFalse( $this->ioHandler->exists( $secondPath ) );
        self::assertEquals(
            md5_file( $this->imageInputPath ),
            md5( fread( $this->ioHandler->getFileResource( $firstPath ), $binaryFile->size ) )
        );

        $newFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'ezplogo2.png';
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->path = $secondPath;
        $updateStruct->setInputStream( fopen( $newFilePath, 'rb' ) );
        $updateStruct->size = filesize( $newFilePath );

        $updatedFile = $this->ioHandler->update( $firstPath, $updateStruct );

        self::assertFalse( $this->ioHandler->exists( $firstPath ), "$firstPath should not exist" );
        self::assertTrue( $this->ioHandler->exists( $secondPath ), "$secondPath should exist" );
        self::assertEquals( $updateStruct->size, $updatedFile->size );
        self::assertEquals(
            md5_file( $newFilePath ),
            md5( fread( $this->ioHandler->getFileResource( $secondPath ), $updatedFile->size ) )
        );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::update
     */
    public function testUpdateMtime()
    {
        $path = 'var/test/updateMtime.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $path );
        $binaryFile = $this->ioHandler->create( $struct );

        $newMtime = new DateTime( 'last week' );
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->mtime = $newMtime;

        $updatedBinaryFile = $this->ioHandler->update( $path, $updateStruct );
        self::assertEquals( $binaryFile->path, $updatedBinaryFile->path );
        self::assertEquals( $binaryFile->ctime, $updatedBinaryFile->ctime );
        self::assertEquals( $binaryFile->size, $updatedBinaryFile->size );

        self::assertEquals( $newMtime, $updatedBinaryFile->mtime );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::update
     */
    public function testUpdateCtime()
    {
        $path = 'var/test/updateMtime.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $path );
        $binaryFile = clone $this->ioHandler->create( $struct );

        $newCtime = new DateTime( 'last week' );
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->ctime = $newCtime;

        $updatedBinaryFile = $this->ioHandler->update( $path, $updateStruct );
        self::assertEquals( $binaryFile->path, $updatedBinaryFile->path );
        self::assertEquals( $binaryFile->mtime, $updatedBinaryFile->mtime );
        self::assertEquals( $binaryFile->size, $updatedBinaryFile->size );

        self::assertEquals( $newCtime, $updatedBinaryFile->ctime );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::update
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testUpdateNonExistingSource()
    {
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->path = 'var/test/testUpdateSourceNotFoundTarget.png';

        $this->ioHandler->update( 'var/test/testUpdateSourceNotFoundSource.png', $updateStruct );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::update
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateTargetExists()
    {
        $firstPath = 'var/test/testUpdateTargetExists-1.gif';
        $secondPath = 'var/test/testUpdateTargetExists-2.png';

        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $firstPath );
        $binaryFile = $this->ioHandler->create( $struct );

        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $secondPath );
        $binaryFile = $this->ioHandler->create( $struct );

        self::assertTrue( $this->ioHandler->exists( $firstPath ) );
        self::assertTrue( $this->ioHandler->exists( $secondPath ) );

        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->path = $secondPath;

        $this->ioHandler->update( $firstPath, $updateStruct );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::exists
     */
    public function testExists()
    {
        $repositoryPath = 'var/test/storage/exists.gif';

        self::assertFalse( $this->ioHandler->exists( $repositoryPath ) );

        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->ioHandler->create( $struct );

        self::assertTrue( $this->ioHandler->exists( $repositoryPath ) );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::delete
     */
    public function testDelete()
    {
        $repositoryPath = 'var/test/storage/delete.gif';

        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->ioHandler->create( $struct );

        self::assertTrue( $this->ioHandler->exists( $repositoryPath ) );

        $this->ioHandler->delete( $repositoryPath );

        self::assertFalse( $this->ioHandler->exists( $repositoryPath ) );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::delete
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteNonExistingFile()
    {
        $this->ioHandler->delete( 'var/test/storage/deleteNonExisting.gif' );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::load
     */
    public function testLoad()
    {
        $repositoryPath = 'var/test/storage/load.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->ioHandler->create( $struct );

        $loadedFile = $this->ioHandler->load( $repositoryPath );

        self::assertInstanceOf( 'eZ\\Publish\\SPI\\IO\\BinaryFile', $loadedFile );

        self::assertEquals( 'var/test/storage/load.gif', $loadedFile->path );
        self::assertEquals( 1928, $loadedFile->size );
        self::assertInstanceOf( 'DateTime', $loadedFile->mtime );
        self::assertInstanceOf( 'DateTime', $loadedFile->ctime );
        self::assertEquals( 'image/gif', $loadedFile->mimeType );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::load
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadNonExistingFile()
    {
        $this->ioHandler->load( 'var/test/storage/loadNotFound.png' );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::getFileResource
     */
    public function testGetFileResource()
    {
        $this->urlFopenPrecheck();
        $repositoryPath = 'var/test/storage/getfileresource.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->ioHandler->create( $struct );

        $resource = $this->ioHandler->getFileResource( $repositoryPath );

        $storedDataSum = md5( fread( $resource, $binaryFile->size ) );
        $originalDataSum = md5( file_get_contents( $this->imageInputPath ) );
        self::assertEquals( $originalDataSum, $storedDataSum );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::getFileResource
     * @expectedException eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testGetFileResourceNonExistingFile()
    {
        $this->ioHandler->getFileResource( 'var/test/testGetFileResourceNonExistingFile.png' );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::getFileContents
     */
    public function testGetFileContents()
    {
        $repositoryPath = 'var/test/testGetFileContents.gif';
        $struct = $this->getCreateStructFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile = $this->ioHandler->create( $struct );

        self::assertEquals( file_get_contents( $this->imageInputPath ), $this->ioHandler->getFileContents( $repositoryPath ) );
    }

    /**
     * @covers \eZ\Publish\SPI\IO\Handler::getFileContents
     * @expectedException eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testGetFileContentsNonExistingFile()
    {
        $this->ioHandler->getFileContents( 'var/test/testGetFileContentsNonExistingFile.gif' );
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
            throw new \Exception( "Could not find/read file: {$localFile}" );
        }

        $struct = new BinaryFileCreateStruct();
        $struct->originalFile = basename( $localFile );
        $struct->size = filesize( $localFile );
        $struct->mimeType = self::getMimeTypeFromPath( $localFile );
        $struct->path = $repositoryPath;

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
