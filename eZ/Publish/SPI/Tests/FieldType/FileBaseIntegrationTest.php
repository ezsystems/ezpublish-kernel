<?php

namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\FieldType;
use eZ\Publish\Core\IO\IOService;
use eZ\Publish\Core\IO\Handler\Filesystem as IOHandler;
use eZ\Publish\Core\IO\MimeTypeDetector\FileInfo as MimeTypeDetector;
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
     * Temporary storage directory
     *
     * @var string
     */
    protected static $storageDir;

    /**
     * Returns the storage dir used by the IOHandler
     *
     * @return string
     */
    abstract protected function getStorageDir();

    /**
     * Returns prefix used by the IOService
     *
     * @return string
     */
    abstract protected function getStoragePrefix();

    /**
     * @return \eZ\Publish\Core\IO\IOService
     */
    public function getIOService()
    {
        return new IOService(
            $this->getIOHandler(),
            $this->getMimeTypeDetector(),
            array( 'prefix' => $this->getStoragePrefix() )
        );
    }

    /**
     * @return IOHandler
     */
    protected function getIOHandler()
    {
        return new IOHandler( $this->getStorageDir() );
    }

    /**
     * Returns MIME type detector to be used.
     *
     * @return \eZ\Publish\SPI\IO\MimeTypeDetector
     */
    protected function getMimeTypeDetector()
    {
        return new MimeTypeDetector;
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

        self::$storageDir = $tmpFile;
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
            self::removeRecursive( self::$storageDir );
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
