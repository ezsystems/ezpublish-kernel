<?php
/**
 * File contains: eZ\Publish\API\Repository\Tests\FieldType\FileBaseIntegrationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FileSystemIterator;

/**
 * Integration test for use field type
 *
 * @group integration
 * @group field-type
 */
abstract class FileBaseIntegrationTest extends BaseIntegrationTest
{
    /**
     * Base install dir
     *
     * @var string
     */
    protected static $installDir;

    /**
     * Storage directory used by the IOHandler
     * @var string
     */
    protected static $storageDir;

    /**
     * Prefix added by IOService to stored files
     * @var string
     */
    protected static $storagePrefix;

    /**
     * Storage dir settings key
     */
    protected static $storageDirConfigKey = 'storage_dir';

    /**
     * If storage data should not be cleaned up
     *
     * @var boolean
     */
    protected static $leaveStorageData = false;

    /**
     * Returns the install dir used by the test
     *
     * @return string
     */
    protected function getInstallDir()
    {
        return self::$installDir;
    }

    /**
     * Returns the storage dir used by the test
     *
     * @return string
     */
    protected function getStorageDir()
    {
        return self::$storageDir . '/' . self::$storagePrefix;
    }

    /**
     * Perform storage directory setup on first execution
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        if ( !isset( self::$installDir ) )
        {
            self::$installDir = $this->getConfigValue( 'install_dir' );
            self::$storageDir = $this->getConfigValue( static::$storageDirConfigKey );
            self::$storagePrefix = $this->getConfigValue( static::$storagePrefixConfigKey);
        }
    }

    /**
     * Tears down the test.
     *
     * Cleans up the storage directory, if it was used
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        self::cleanupStorageDir();
    }

    /**
     * Returns an iterator over the full storage dir.
     *
     * @return Iterator
     */
    protected static function getStorageDirIterator()
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                self::$installDir . '/' . self::$storageDir,
                FileSystemIterator::KEY_AS_PATHNAME | FileSystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );
    }

    /**
     * Removes the given directory path recursively
     *
     * @param string $dir
     *
     * @return void
     */
    protected static function cleanupStorageDir()
    {
        if ( self::$installDir == null || self::$storageDir == null || self::$leaveStorageData )
        {
            // Nothing to do
            return;
        }

        try
        {
            $iterator = self::getStorageDirIterator();

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
        catch ( \UnexpectedValueException $e )
        {
            // The directory to cleanup just doesn't exist
        }

    }
}
