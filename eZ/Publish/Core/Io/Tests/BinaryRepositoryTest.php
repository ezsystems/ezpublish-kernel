<?php
/**
 * File containing the QueryBuilderTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\Tests;
use ezp\Base\ServiceContainer,
    ezp\Base\Configuration,
    ezp\Io\Storage\InMemory,
    ezp\Io\BinaryFile,
    ezp\Io\BinaryFileCreateStruct,
    ezp\Io\BinaryFileUpdateStruct,
    ezp\Io\ContentType,
    DateTime;

class BinaryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Binary Repository instance
     * @var \ezp\Io\Service
     */
    protected $binaryService;

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

    public function setUp()
    {
        $sc = new ServiceContainer(
            Configuration::getInstance('service')->getAll(),
            array(
                '@persistence_handler' => new \ezp\Persistence\Storage\InMemory\Handler(),
                '@io_handler' => new InMemory(),
            )
        );
        $this->binaryService = $sc->getRepository()->getIoService();
        $this->imageInputPath = __DIR__ . DIRECTORY_SEPARATOR . 'ezplogo.gif';
    }

    public function tearDown()
    {
        unset( $this->binaryService );
    }

    public function testCreate()
    {
        $repositoryPath = 'var/test/storage/images/ezplogo.gif';
        $binaryFile = $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );

        self::assertInstanceOf( 'ezp\\Io\\BinaryFile', $binaryFile );
        self::assertEquals( $repositoryPath, $binaryFile->path );
        self::assertEquals( 1928, $binaryFile->size );
        self::assertInstanceOf( 'DateTime', $binaryFile->mtime );
        self::assertNotEquals( 0, $binaryFile->mtime->getTimestamp() );
        self::assertEquals( new ContentType( 'image', 'gif' ), $binaryFile->contentType );
    }

    /**
     * @expectedException ezp\Io\Exception\PathExists
     */
    public function testCreatePathExists()
    {
        $repositoryPath = 'var/test/storage/images/testCreateFileExists.gif';

        $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );
        $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );
    }

    public function testExists()
    {
        $repositoryPath = 'var/test/storage/exists.gif';

        self::assertFalse( $this->binaryService->exists( $repositoryPath ) );

        $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );

        self::assertTrue( $this->binaryService->exists( $repositoryPath ) );
    }

    public function testDelete()
    {
        $repositoryPath = 'var/test/storage/delete.gif';

        $binaryFileCreateStruct = $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );

        self::assertTrue( $this->binaryService->exists( $repositoryPath ) );

        $this->binaryService->delete( $repositoryPath );

        self::assertFalse( $this->binaryService->exists( $repositoryPath ) );
    }

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDeleteNonExistingFile()
    {
        $this->binaryService->delete( 'var/test/storage/deleteNonExisting.gif' );
    }

    /**
     * @expectedException ezp\Base\Exception\NotFound
     */
    public function testLoadNonExistinFile()
    {
        $this->binaryService->load( 'var/test/storage/loadNotFound.png' );
    }

    public function testLoad()
    {
        $repositoryPath = 'var/test/storage/load.gif';
        $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );

        $loadedFile = $this->binaryService->load( $repositoryPath );

        self::assertInstanceOf( 'ezp\\Io\\BinaryFile', $loadedFile );

        self::assertEquals( 'var/test/storage/load.gif', $loadedFile->path );
        self::assertEquals( 1928, $loadedFile->size );
        self::assertInstanceOf( 'DateTime', $loadedFile->mtime );
        self::assertInstanceOf( 'DateTime', $loadedFile->ctime );
        self::assertEquals( new ContentType( 'image', 'gif' ), $loadedFile->contentType );
    }

    /**
     * @expectedException ezp\Base\Exception\NotFound
     */
    public function testGetFileResourceNonExistinFile()
    {
        $this->binaryService->getFileResource( 'var/test/testGetFileResourceNonExistinFile.png' );
    }

    public function testGetFileResource()
    {
        $this->urlFopenPrecheck();
        $repositoryPath = 'var/test/storage/getfileresource.gif';
        $binaryFile = $this->createFileWithPath( $repositoryPath );

        $resource = $this->binaryService->getFileResource( $repositoryPath );

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

        $this->binaryService->update( 'var/test/testUpdateSourceNotFoundSource.png', $updateStruct );
    }

    public function testUpdate()
    {
        $this->urlFopenPrecheck();
        $firstPath = 'var/test/update-before.gif';
        $secondPath = 'var/test/update-after.png';

        $binaryFile = $this->createFileWithPath( $firstPath );

        self::assertTrue( $this->binaryService->exists( $firstPath ) );
        self::assertFalse( $this->binaryService->exists( $secondPath ) );
        self::assertEquals(
            md5_file( $this->imageInputPath ),
            md5( fread( $this->binaryService->getFileResource( $firstPath ), $binaryFile->size ) )
        );

        $newFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'ezplogo2.png';
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->path = $secondPath;
        $updateStruct->setInputStream( fopen( $newFilePath, 'rb' ) );
        $updateStruct->size = filesize( $newFilePath );

        $updatedFile = $this->binaryService->update( $firstPath, $updateStruct );

        self::assertFalse( $this->binaryService->exists( $firstPath ), "$firstPath should not exist" );
        self::assertTrue( $this->binaryService->exists( $secondPath ), "$secondPath should exist" );
        self::assertEquals( $updateStruct->size, $updatedFile->size );
        self::assertEquals(
            md5_file( $newFilePath ),
            md5( fread( $this->binaryService->getFileResource( $secondPath ), $updatedFile->size ) )
        );
    }

    public function testUpdateMtime()
    {
        $path = 'var/test/updateMtime.gif';
        $binaryFile = clone $this->createFileWithPath( $path );

        $newMtime = new DateTime( 'last week' );
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->mtime = $newMtime;

        $updatedBinaryFile = $this->binaryService->update( $path, $updateStruct );
        self::assertEquals( $binaryFile->path, $updatedBinaryFile->path );
        self::assertEquals( $binaryFile->ctime, $updatedBinaryFile->ctime );
        self::assertEquals( $binaryFile->size, $updatedBinaryFile->size );

        self::assertEquals( $newMtime, $updatedBinaryFile->mtime );
    }

    public function testUpdateCtime()
    {
        $path = 'var/test/updateMtime.gif';
        $binaryFile = clone $this->createFileWithPath( $path );

        $newCtime = new DateTime( 'last week' );
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->ctime = $newCtime;

        $updatedBinaryFile = $this->binaryService->update( $path, $updateStruct );
        self::assertEquals( $binaryFile->path, $updatedBinaryFile->path );
        self::assertEquals( $binaryFile->mtime, $updatedBinaryFile->mtime );
        self::assertEquals( $binaryFile->size, $updatedBinaryFile->size );

        self::assertEquals( $newCtime, $updatedBinaryFile->ctime );
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

        self::assertTrue( $this->binaryService->exists( $firstPath ) );
        self::assertTrue( $this->binaryService->exists( $secondPath ) );

        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->path = $secondPath;

        $this->binaryService->update( $firstPath, $updateStruct );
    }

    public function testGetFileContents()
    {
        $path = 'var/test/testGetFileContents.gif';
        $this->createFileWithPath( $path );

        self::assertEquals( file_get_contents( $this->imageInputPath ), $this->binaryService->getFileContents( $path ) );
    }

    /**
     * @expectedException ezp\Base\Exception\NotFound
     */
    public function testGetFileContentsNonExistingFile()
    {
        $this->binaryService->getFileContents( 'var/test/testGetFileContentsNonExistingFile.gif' );
    }

    /**
     * Creates a new BinaryFile on the repository using $this->inputImagePath as the source
     * @param string $path Path to create the file at on the repository
     * @return BinaryFile The created file
     */
    private function createFileWithPath( $path )
    {
        return $this->binaryService->createFromLocalFile( $this->imageInputPath, $path );
    }

    private function urlFopenPrecheck()
    {
        if ( ini_get( "allow_url_fopen" ) != 1 )
            $this->markTestSkipped( "allow_url_fopen must be 'On' for this test." );
    }
}
?>
