<?php
/**
 * File containing the QueryBuilderTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\Tests;
use ezp\Base\BinaryRepository,
    ezp\Io\BinaryFile, ezp\Io\BinaryFileCreateStruct, ezp\Io\BinaryFileUpdateStruct;

class BinaryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->binaryRepository = new BinaryRepository( 'inmemory' );
        $this->imageInputPath = __DIR__ . DIRECTORY_SEPARATOR . 'ezplogo.gif';

        // Create one file for later use
        $binaryFileCreateStruct = $this->binaryRepository->createFromLocalFile( $this->imageInputPath );
        $binaryFileCreateStruct->path = 'var/test/storage/images/testfile.gif';
        $this->testFile = $this->binaryRepository->create( $binaryFileCreateStruct );
   }

    public function tearDown()
    {
        $this->binaryRepository->delete( $this->testFile->path );
        unset( $this->binaryRepository );
    }

    /**
     * Tests creation of a new file
     */
    public function testCreate()
    {
        $binaryFileCreateStruct = $this->binaryRepository->createFromLocalFile( $this->imageInputPath );
        $repositoryPath = 'var/test/storage/images/ezplogo.gif';
        $binaryFileCreateStruct->path = $repositoryPath;
        $binaryFile = $this->binaryRepository->create( $binaryFileCreateStruct );

        self::assertInstanceOf( 'ezp\Io\BinaryFile', $binaryFile );
        self::assertEquals( $repositoryPath, $binaryFile->path );
        self::assertEquals( $binaryFileCreateStruct->size, $binaryFile->size );
        self::assertEquals( $binaryFileCreateStruct->mtime, $binaryFile->mtime );
        self::assertEquals( $binaryFileCreateStruct->ctime, $binaryFile->ctime );
        self::assertEquals( $binaryFileCreateStruct->originalFile, $binaryFile->originalFile );
        self::assertEquals( $binaryFileCreateStruct->contentType, $binaryFile->contentType );
    }

    public function testExists()
    {
        $repositoryPath = 'var/test/storage/exists.gif';
        self::assertFalse( $this->binaryRepository->exists( $repositoryPath ) );
        self::assertTrue( $this->binaryRepository->exists( $this->testFile->path ) );
    }

    public function testDelete()
    {
        $repositoryPath = 'var/test/storage/delete.gif';

        $binaryFileCreateStruct = $this->binaryRepository->createFromLocalFile( $this->imageInputPath );
        $binaryFileCreateStruct->path = $repositoryPath;
        $this->binaryRepository->create( $binaryFileCreateStruct );

        self::assertTrue( $this->binaryRepository->exists( $repositoryPath ) );

        $this->binaryRepository->delete( $repositoryPath );

        self::assertFalse( $this->binaryRepository->exists( $repositoryPath ) );
    }

    public function testLoad()
    {
        $loadedFile = $this->binaryRepository->load( $this->testFile->path );

        self::assertInstanceOf( 'ezp\Io\BinaryFile', $loadedFile );
        self::assertEquals( $this->testFile->path, $loadedFile->path );
        self::assertEquals( $this->testFile->size, $loadedFile->size );
        self::assertEquals( $this->testFile->mtime, $loadedFile->mtime );
        self::assertEquals( $this->testFile->ctime, $loadedFile->ctime );
        self::assertEquals( $this->testFile->originalFile, $loadedFile->originalFile );
        self::assertEquals( $this->testFile->contentType, $loadedFile->contentType );
    }

    public function testGetFileResource()
    {
        $resource = $this->binaryRepository->getFileResource( $this->testFile );

        $storedDataSum = md5( fread( $resource, $this->testFile->size ) );
        $originalDataSum = md5( file_get_contents( $this->imageInputPath ) );
        self::assertEquals( $originalDataSum, $storedDataSum );
    }

    public function testUpdate()
    {
        $firstPath = 'var/storage/images/update-before.gif';
        $secondPath = 'var/storage/images/update-after.png';

        $createStruct = $this->binaryRepository->createFromLocalFile( $this->imageInputPath );
        $createStruct->path = $firstPath;
        $binaryFile = $this->binaryRepository->create( $createStruct );

        self::assertTrue( $this->binaryRepository->exists( $firstPath ) );
        self::assertFalse( $this->binaryRepository->exists( $secondPath ) );
        self::assertEquals( md5_file( $this->imageInputPath ), md5( fread( $this->binaryRepository->getFileResource( $binaryFile ), $binaryFile->size ) ) );

        $newFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'ezplogo2.png';
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->path = $secondPath;
        $updateStruct->setInputStream( fopen( $newFilePath, 'rb' ) );
        $updateStruct->size = filesize( $newFilePath );

        $updatedFile = $this->binaryRepository->update( $firstPath, $updateStruct );

        self::assertFalse( $this->binaryRepository->exists( $firstPath ), "$firstPath should not exist" );
        self::assertTrue( $this->binaryRepository->exists( $secondPath ), "$secondPath should exist" );
        self::assertEquals( md5_file( $newFilePath ), md5( fread( $this->binaryRepository->getFileResource( $updatedFile ), $updateStruct->size ) ) );
    }

    /**
     * Binary Repository instance
     * @var \ezp\Base\BinaryRepository
     */
    protected $binaryRepository;

    /**
     * Test image file
     * @var string
     */
    protected $imageInputPath;

    /**
     * Repository file available for testing
     * @var BinaryFile
     */
    protected $testFile;
}
?>
