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
    ezp\Io\BinaryFile, ezp\Io\BinaryFileCreateStruct, ezp\Io\BinaryFileUpdateStruct,
    ezp\Io\ContentType;

class BinaryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->binaryRepository = new BinaryRepository( 'inmemory' );
        $this->imageInputPath = __DIR__ . DIRECTORY_SEPARATOR . 'ezplogo.gif';
   }

    public function tearDown()
    {
        unset( $this->binaryRepository );
    }

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
        // self::assertEquals( $binaryFileCreateStruct->ctime, $binaryFile->ctime );
        self::assertEquals( $binaryFileCreateStruct->contentType, $binaryFile->contentType );
    }

    public function testExists()
    {
        $repositoryPath = 'var/test/storage/exists.gif';
        $binaryFileCreateStruct = $this->binaryRepository->createFromLocalFile( $this->imageInputPath );
        $binaryFileCreateStruct->path = $repositoryPath;

        self::assertFalse( $this->binaryRepository->exists( $repositoryPath ) );

        $this->binaryRepository->create( $binaryFileCreateStruct );

        self::assertTrue( $this->binaryRepository->exists( $repositoryPath ) );
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

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDeleteNonExistingFile()
    {
        $this->binaryRepository->delete( 'var/test/storage/deleteNonExisting.gif' );
    }

    /**
     * @expectedException ezp\Base\Exception\NotFound
     */
    public function testLoadNonExistinFile()
    {
        $this->binaryRepository->load( 'var/test/storage/loadNotFound.png' );
    }

    public function testLoad()
    {
        $repositoryPath = 'var/test/storage/load.gif';
        $binaryFileCreateStruct = $this->binaryRepository->createFromLocalFile( $this->imageInputPath );
        $binaryFileCreateStruct->path = $repositoryPath;
        $this->binaryRepository->create( $binaryFileCreateStruct );

        $loadedFile = $this->binaryRepository->load( $repositoryPath );

        self::assertInstanceOf( 'ezp\Io\BinaryFile', $loadedFile );

        self::assertEquals( 'var/test/storage/load.gif', $loadedFile->path );
        self::assertEquals( 1928, $loadedFile->size );
        self::assertInstanceOf( '\DateTime', $loadedFile->mtime );
        self::assertInstanceOf( '\DateTime', $loadedFile->ctime );
        self::assertEquals( new ContentType( 'image', 'gif' ), $loadedFile->contentType );
    }

    /**
     * @expectedException ezp\Base\Exception\NotFound
     */
    public function testGetFileResourceNonExistinFile()
    {
        $this->binaryRepository->getFileResource( 'var/test/testGetFileResourceNonExistinFile.png' );
    }

    public function testGetFileResource()
    {
        $repositoryPath = 'var/test/storage/getfileresource.gif';
        $binaryFile = $this->createFileWithPath( $repositoryPath );

        $resource = $this->binaryRepository->getFileResource( $repositoryPath );

        $storedDataSum = md5( fread( $resource, $binaryFile->size ) );
        $originalDataSum = md5( file_get_contents( $this->imageInputPath ) );
        self::assertEquals( $originalDataSum, $storedDataSum );
    }

    /**
     * @expectedException ezp\Base\Exception\NotFound
     */
    public function testUpdateNonExistingSource()
    {
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->path = 'var/test/testUpdateSourceNotFoundTarget.png';

        $this->binaryRepository->update( 'var/test/testUpdateSourceNotFoundSource.png', $updateStruct );
    }

    public function testUpdate()
    {
        $firstPath = 'var/test/update-before.gif';
        $secondPath = 'var/test/update-after.png';

        $binaryFile = $this->createFileWithPath( $firstPath );

        self::assertTrue( $this->binaryRepository->exists( $firstPath ) );
        self::assertFalse( $this->binaryRepository->exists( $secondPath ) );
        self::assertEquals(
            md5_file( $this->imageInputPath ),
            md5( fread( $this->binaryRepository->getFileResource( $firstPath ), $binaryFile->size ) )
        );

        $newFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'ezplogo2.png';
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->path = $secondPath;
        $updateStruct->setInputStream( fopen( $newFilePath, 'rb' ) );
        $updateStruct->size = filesize( $newFilePath );

        $updatedFile = $this->binaryRepository->update( $firstPath, $updateStruct );

        self::assertFalse( $this->binaryRepository->exists( $firstPath ), "$firstPath should not exist" );
        self::assertTrue( $this->binaryRepository->exists( $secondPath ), "$secondPath should exist" );
        self::assertEquals( $updateStruct->size, $updatedFile->size );
        self::assertEquals(
            md5_file( $newFilePath ),
            md5( fread( $this->binaryRepository->getFileResource( $secondPath ), $updatedFile->size ) )
        );
    }

    /**
     * @expectedException ezp\Io\Exception\PathExists
     */
    public function testUpdateTargetExists()
    {
        $firstPath = 'var/test/testUpdateTargetExists-1.gif';
        $secondPath = 'var/test/testUpdateTargetExists-2.png';

        $binaryFile = $this->createFileWithPath( $firstPath );
        $binaryFile = $this->createFileWithPath( $secondPath );

        self::assertTrue( $this->binaryRepository->exists( $firstPath ) );
        self::assertTrue( $this->binaryRepository->exists( $secondPath ) );

        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->path = $secondPath;

        $this->binaryRepository->update( $firstPath, $updateStruct );
    }

    /**
     * Creates a new BinaryFile on the repository using $this->inputImagePath as the source
     * @param string $path Path to create the file at on the repository
     * @return BinaryFile The created file
     */
    private function createFileWithPath( $path )
    {
        $binaryFileCreateStruct = $this->binaryRepository->createFromLocalFile( $this->imageInputPath );
        $binaryFileCreateStruct->path = $path;
        return $this->binaryRepository->create( $binaryFileCreateStruct );
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
