<?php
/**
 * File containing a Io Handler test
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Tests\Handler;

use eZ\Publish\Core\IO\Handler\Filesystem as FilesystemHandler;
use eZ\Publish\Core\IO\Tests\Handler\Base as BaseHandlerTest;
use FileSystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Handler test
 */
class FilesystemTest extends BaseHandlerTest
{
    private static $storageDir = 'var/filesystem';

    public static function setupBeforeClass()
    {
        if ( !file_exists( self::$storageDir ) )
        {
            mkdir( self::$storageDir );
        }
    }

    public function setUp()
    {
        self::cleanUpStorageDir();
        parent::setUp();
    }

    public function tearDown()
    {
        self::cleanUpStorageDir();
        parent::tearDown();
    }

    /**
     * @return \eZ\Publish\SPI\IO\Handler
     */
    protected function getIOHandler( $path = null )
    {
        return new FilesystemHandler( $path ?: self::$storageDir );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testConstructDirectoryNotFound()
    {
        $this->getIOHandler( 'some/path' );
    }

    public function testConstructDirectoryNotWritable()
    {
        $dir = self::$storageDir . DIRECTORY_SEPARATOR . 'dir';
        mkdir( $dir );
        chmod( $dir, 0000 );
        try
        {
            $this->getIOHandler( $dir );
        }
        catch ( \RuntimeException $e )
        {
            $gotException = true;
        }
        chmod( $dir, 0775 );
        rmdir( $dir );

        self::assertTrue( isset( $gotException ), "No exception was thrown" );
    }

    private function cleanupStorageDir()
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                self::$storageDir,
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
    }
}
