<?php

namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\FieldType;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FileSystemIterator;
use eZ\Publish\Core\Base\ConfigurationManager;
use eZ\Publish\Core\Base\ServiceContainer;
use Symfony\Component\Filesystem\Filesystem as FilesystemComponent;

abstract class FileBaseIntegrationTest extends BaseIntegrationTest
{
    /**
     * Temporary storage directory
     *
     * @var string
     */
    protected static $tmpDir;

    /**
     * Returns prefix used by the IOService
     *
     * @return string
     */
    abstract protected function getStoragePrefix();

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

        $storageDir = self::$tmpDir . '/var/ezdemo_site/storage';
        if ( !file_exists( $storageDir ) )
        {
            $fs = new FilesystemComponent();
            $fs->mkdir( $storageDir );
        }
    }

    /**
     * Removes the temp dir
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        self::removeRecursive( self::$tmpDir );
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

    protected function getContainer()
    {
        // get configuration config
        if ( !( $settings = include 'config.php' ) )
        {
            throw new \RuntimeException(
                'Could not find config.php, please copy config.php-DEVELOPMENT to config.php customize to your needs!'
            );
        }

        // load configuration uncached
        $configManager = new ConfigurationManager(
            array_merge_recursive(
                $settings,
                array(
                    'base' => array(
                        'Configuration' => array(
                            'UseCache' => false
                        )
                    )
                )
            ),
            $settings['base']['Configuration']['Paths']
        );

        $serviceSettings = $configManager->getConfiguration( 'service' )->getAll();
        $serviceSettings['legacy_db_handler']['arguments']['dsn'] = $this->getDsn();
        $serviceSettings['parameters']['io_root_dir'] = self::$tmpDir;

        return new ServiceContainer(
            $serviceSettings,
            array()
        );
    }

    /**
     * Asserts that the IO File with uri $uri exists
     * @param string $uri
     */
    protected function assertIOUriExists( $uri )
    {
        $this->assertTrue(
            file_exists( self::$tmpDir . '/' . $uri ),
            "Stored file uri $uri does not exist"
        );
    }

    /**
     * Asserts that the IO File with id $id exists
     * @param string $id
     */
    protected function assertIOIdExists( $id )
    {
        $path = $this->getPathFromId( $id );
        $this->assertTrue(
            file_exists( $path ),
            "Stored file $path does not exists"
        );
    }

    /**
     * Returns the physical path to the file with id $id
     */
    protected function getPathFromId( $id )
    {
        return $this->getStorageDir() . '/' . $this->getStoragePrefix() . '/' . $id;
    }

    protected function getStorageDir()
    {
        return ( self::$tmpDir ? self::$tmpDir . '/' : '' ) . $this->getContainer()->getVariable( 'storage_dir' );
    }

    protected function getFilesize( $binaryFileId )
    {
        return filesize( $this->getPathFromId( $binaryFileId ) );
    }
}
