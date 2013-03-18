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
     * List of path in config:storage_dir that must not be deleted, and must be ignored in these tests
     * Workaround for FieldType vs. Repository tests. The repository tests require those files since they
     * match the ones referenced in the database fixtures used by Services tests.
     * @var array
     */
    protected static $ignoredPathList;

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
        return self::$storageDir;
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

            self::setUpIgnoredPath( $this->getConfigValue( 'ignored_storage_files' ) );
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
                    if ( !self::isIgnoredPath( dirname( $path ) ) )
                        rmdir( $path );
                }
                else if ( !self::isIgnoredPath( $path ) )
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

    protected static function setUpIgnoredPath( $ignoredFiles )
    {
        foreach ( $ignoredFiles as $ignoredFile )
        {
            $pathPartsArray = explode( DIRECTORY_SEPARATOR, $ignoredFile );
            foreach ( $pathPartsArray as $index => $directoryPart )
            {
                if ( $directoryPart == '' )
                    continue;
                $partPath = implode(
                    DIRECTORY_SEPARATOR,
                    array_slice( $pathPartsArray, 0, $index + 1 )
                );
                self::$ignoredPathList[realpath( $partPath )] = true;
            }
        }
    }

    /**
     * Checks if $path must be excluded from filesystem cleanup
     * @param string $path
     * @return bool
     */
    protected static function isIgnoredPath( $path )
    {
        return isset( self::$ignoredPathList[realpath( $path )] );
    }
}
