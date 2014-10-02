<?php
/**
 * File containing a Io Handler test
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Tests\Handler;

use eZ\Publish\Core\IO\Handler\Filesystem as FilesystemHandler;
use eZ\Publish\Core\IO\MimeTypeDetector\FileInfo as FileInfoMimeTypeDetector;
use eZ\Publish\Core\IO\Tests\Handler\Base as BaseHandlerTest;
use FileSystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Handler test
 */
class FilesystemTest extends BaseHandlerTest
{
    protected static $storageDir = 'var/filesystem';

    protected static $rootDir = null;

    public static function setupBeforeClass()
    {
        if ( !file_exists( self::$storageDir ) )
        {
            mkdir( self::$storageDir );
        }
    }

    public function setUp()
    {
        self::cleanupDir();
        parent::setUp();
    }

    public function tearDown()
    {
        self::cleanupDir();
        parent::tearDown();
    }

    /**
     * @return \eZ\Publish\SPI\IO\Handler
     */
    protected function getIOHandler( $storageDir = null, $rootDir = null )
    {
        $storageDir = $storageDir ?: self::$storageDir;
        $rootDir = $rootDir ?: self::$rootDir;

        $options = array( 'storage_dir' => $storageDir );
        if ( $rootDir !== null )
        {
            $options['root_dir'] = $rootDir;
        }

        return new FilesystemHandler( new FileInfoMimeTypeDetector(), $options );
    }

    public function testConstructDirectoryRootDirDoesNotExist()
    {
        $rootDir = 'var/otherdir';
        $this->getIOHandler( $rootDir );
        $this->assertTrue( file_exists( $rootDir ) && is_dir( $rootDir ), "Failed asserting that $rootDir was created" );

        self::cleanupDir( $rootDir, true );
    }

    public function testConstructStorageDirectoryNotWritable()
    {
        $storageDir = self::$storageDir . DIRECTORY_SEPARATOR . 'dir';
        mkdir( $storageDir );
        chmod( $storageDir, 0000 );
        try
        {
            $this->getIOHandler( $storageDir );
        }
        catch ( \RuntimeException $e )
        {
            $gotException = true;
        }
        chmod( $storageDir, 0775 );
        rmdir( $storageDir );

        self::assertTrue( isset( $gotException ), "No exception was thrown" );
    }

    private function cleanupDir( $dir = null, $removeDirectory = false )
    {
        $dir = $dir ?: self::$storageDir;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir,
                FileSystemIterator::KEY_AS_PATHNAME | FileSystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO
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

        if ( $removeDirectory )
        {
            rmdir( $dir );
        }
    }
}
