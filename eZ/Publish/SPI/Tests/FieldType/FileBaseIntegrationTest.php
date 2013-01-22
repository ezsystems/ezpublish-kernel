<?php

namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\FieldType;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FileSystemIterator;

abstract class FileBaseIntegrationTest extends BaseIntegrationTest
{
    /**
     * If the temporary directory should be removed after the tests.
     *
     * @var boolean
     */
    protected static $removeTmpDir = false;

    /**
     * Temporary directory
     *
     * @var string
     */
    protected static $tmpDir;

    /**
     * Returns the storage dir used by the file service
     *
     * @return string
     */
    abstract protected function getStorageDir();

    /**
     * Returns the storage identifier prefix used by the file service
     *
     * @return void
     */
    abstract protected function getStorageIdentifierPrefix();

    /**
     * Returns a file service to be used.
     *
     * @return FileService
     */
    protected function getFileService()
    {
        return new FieldType\FileService\LocalFileService(
            $this->getTempDir(),
            $this->getStorageDir(),
            $this->getStorageIdentifierPrefix()
        );
    }

    /**
     * Returns MIME type detector to be used.
     *
     * @return \eZ\Publish\Core\FieldType\BinaryBase\MimeTypeDetector
     */
    protected function getMimeTypeDetector()
    {
        return new FieldType\BinaryBase\MimeTypeDetector\FileInfoDetector();
    }

    /**
     * Returns the temporary directory to be used for file storage
     *
     * @return string
     */
    protected function getTempDir()
    {
        return self::$tmpDir;
    }

    /**
     * Sets up a temporary directory and stores its path in self::$tmpDir
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        $calledClass = get_called_class();

        $tmpFile = tempnam(
            sys_get_temp_dir(),
            'eZ_' . substr( $calledClass, strrpos( $calledClass, '\\' ) + 1 )
        );

        // Convert file into directory
        unlink( $tmpFile );
        mkdir( $tmpFile );

        self::$tmpDir = $tmpFile;
    }

    /**
     * Removes the temp dir, if self::$removeTmpDir is true
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        if ( self::$removeTmpDir )
        {
            self::removeRecursive( self::$tmpDir );
        }
    }

    /**
     * Removes the given directory path recursively
     *
     * @param string $dir
     *
     * @return void
     */
    protected static function removeRecursive( $dir )
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir,
                FileSystemIterator::KEY_AS_PATHNAME | FileSystemIterator::SKIP_DOTS | FileSystemIterator::CURRENT_AS_FILEINFO
            ),
            RecursiveIteratorIterator::CHILD_FIRST
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

        rmdir( $dir );
    }
}
