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
    private $storageDir = 'var/filesystem';

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
        return new FilesystemHandler( $path ?: $this->storageDir );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testConstructDirectoryNotFound()
    {
        $this->getIOHandler( '/some/path' );
    }

    public function testConstructDirectoryNotWritable()
    {
        $dir = $this->storageDir . DIRECTORY_SEPARATOR . 'dir';
        mkdir( $dir );
    }

    private function cleanupStorageDir()
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->storageDir,
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
