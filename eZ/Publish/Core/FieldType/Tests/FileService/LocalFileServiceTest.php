<?php
/**
 * File containing the LocalFileServiceTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests\FileService;

use eZ\Publish\Core\FieldType\FileService\LocalFileService;

/**
 * @group fieldType
 * @group ezimage
 */
class LocalFileServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Temporary directory to use for operations
     *
     * @var string
     */
    protected $tmpDir;

    /**
     * Storage path for the file service
     *
     * @var string
     */
    protected $storagePath = 'var/storage';

    /**
     * Prefix for storage identifiers
     *
     * @var string
     */
    protected $storageIdentifierPrefix = 'my/fancy/site';

    /**
     * File service under test
     *
     * @var LocalFileService
     */
    protected $fileService;

    /**
     * Creates a dedicated $tmpDir for each test
     *
     * @return void
     */
    protected function getTempDir()
    {
        if ( !isset( $this->tmpDir ) )
        {
            $tmpPath = tempnam(
                sys_get_temp_dir(),
                'eZ-Image-Field-Type'
            );
            unlink( $tmpPath );
            mkdir( $tmpPath );

            $this->tmpDir = $tmpPath;
        }
        return $this->tmpDir;
    }

    /**
     * Returns the storage dir used by the file handler
     *
     * @return string
     */
    protected function getStorageDir()
    {
        return $this->getTempDir() . '/' . $this->storagePath;
    }

    /**
     * Cleans up the $tmpDir
     *
     * @return void
     */
    protected function tearDown()
    {
        if ( $this->tmpDir === null )
        {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->getTempDir(),
                \FileSystemIterator::KEY_AS_PATHNAME | \FileSystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_FILEINFO
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ( $iterator as $path => $fileInfo )
        {
            if ( $fileInfo->isDir() )
            {
                rmdir( $path );
            }
            else
            {
                unlink( $path );
            }
        }

        rmdir( $this->getTempDir() );
    }

    /**
     * Returns the file service under test
     *
     * @return LocalFileService
     */
    protected function getFileService()
    {
        if ( !isset( $this->fileService ) )
        {
            $this->fileService = new LocalFileService(
                $this->getTempDir(),
                $this->storagePath,
                $this->storageIdentifierPrefix
            );
        }
        return $this->fileService;
    }

    public function testGetStorageIdentifier()
    {
        $this->assertEquals(
            'my/fancy/site/some/sindelfingen/file.foo',
            $this->getFileService()->getStorageIdentifier( 'some/sindelfingen/file.foo' )
        );
    }

    public function testStoreExternalFile()
    {
        $externalFile = __FILE__;
        $target = 'some/target/file.foo';

        $fileService = $this->getFileService();

        $storedPath = $fileService->storeFile(
            $externalFile,
            $fileService->getStorageIdentifier( $target )
        );

        $this->assertEquals(
            sprintf(
                '%s/%s',
                $this->storageIdentifierPrefix,
                $target
            ),
            $storedPath
        );

        return $storedPath;
    }

    public function testStoreExternalFileExists()
    {
        $storedPath = $this->testStoreExternalFile();

        $this->assertTrue(
            file_exists( $this->getStorageDir() . '/' . $storedPath )
        );
    }

    public function testStoreExternalFilePermissions()
    {
        $storedPath = $this->testStoreExternalFile();

        $this->assertEquals(
            0664,
            fileperms( $this->getStorageDir() . '/' . $storedPath ) & 0777
        );
    }

    public function testStoreExternalFileDirectoryPermissions()
    {
        $storedPath = $this->testStoreExternalFile();

        $this->assertEquals(
            0775,
            fileperms( dirname( $this->getStorageDir() . '/' . $storedPath ) ) & 0777
        );
    }

    public function testStoreExternalFileContent()
    {
        $storedPath = $this->testStoreExternalFile();

        $this->assertSame(
            file_get_contents( __FILE__ ),
            file_get_contents( $this->getStorageDir() . '/' . $storedPath )
        );
    }

    public function testStoreInternalFile()
    {
        $internalFile = $this->testStoreExternalFile();

        $fileService = $this->getFileService();

        $storedPath = $fileService->storeFile( $internalFile, 'new/destination/bar.foo' );

        $this->assertTrue(
            file_exists( $this->getStorageDir() . '/' . $storedPath )
        );

        return $storedPath;
    }

    public function testStoreInternalFileContent()
    {
        $storedPath = $this->testStoreInternalFile();

        $this->assertSame(
            file_get_contents( __FILE__ ),
            file_get_contents( $this->getStorageDir() . '/' . $storedPath )
        );
    }

    public function testRemovePathFile()
    {
        $storedPath = $this->testStoreExternalFile();

        $fileService = $this->getFileService();

        $fileService->remove( $storedPath );

        $this->assertFalse(
            file_exists( $this->getStorageDir() . '/' . $storedPath )
        );
    }

    public function testRemovePathDirNonRecursive()
    {
        $storedPath = $this->testStoreExternalFile();

        $fileService = $this->getFileService();

        $fileService->remove( $storedPath );
        $fileService->remove( dirname( $storedPath ) );

        $this->assertFalse(
            file_exists( dirname( $this->getStorageDir() . '/' . $storedPath ) )
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRemovePathDirNonRecursiveFailure()
    {
        $storedPath = $this->testStoreExternalFile();

        $fileService = $this->getFileService();

        $fileService->remove( dirname( $storedPath ) );
    }

    public function testRemovePathDirRecursive()
    {
        $storedPath = $this->testStoreExternalFile();

        $fileService = $this->getFileService();

        $fileService->remove( dirname( $storedPath ), true );

        $this->assertFalse(
            file_exists( dirname( $this->getStorageDir() . '/' . $storedPath ) )
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function testRemoveEmptyPath()
    {
        $emptyPath = '';

        $fileService = $this->getFileService();

        $fileService->remove( $emptyPath );
    }

    public function testGetFileSize()
    {
        $storedPath = $this->testStoreExternalFile();

        $fileService = $this->getFileService();

        $size = $fileService->getFileSize( $storedPath );

        $this->assertEquals(
            filesize( __FILE__ ),
            $size
        );
    }
}
