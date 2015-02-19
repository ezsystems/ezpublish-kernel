<?php

namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\FieldType;
use eZ\Publish\Core\IO\IOServiceInterface;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FileSystemIterator;
use Symfony\Component\Filesystem\Filesystem as FilesystemComponent;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Publish\Core\Base\Container\Compiler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

abstract class FileBaseIntegrationTest extends BaseIntegrationTest
{
    /**
     * Temporary storage directory
     *
     * @var string
     */
    protected static $tmpDir;

    /** @var IOServiceInterface */
    protected $ioService;

    /**
     * @see EZP-23534
     */
    public function testLoadingContentWithMissingFileWorks()
    {
        $contentType = $this->createContentType();
        $content = $this->createContent( $contentType, $this->getInitialValue() );

        // delete the binary file object
        $this->deleteStoredFile( $content );

        // try loading the content again. It should work even though the image isn't physically here
        $this->getCustomHandler()->contentHandler()->load( $content->versionInfo->contentInfo->id, 1 );
    }

    /**
     * Deletes the binary file stored in the field
     *
     * @param $content
     *
     * @return mixed
     */
    protected function deleteStoredFile( $content )
    {
        return $this->ioService->deleteBinaryFile(
            $this->ioService->loadBinaryFile( $content->fields[1]->value->externalData['id'] )
        );
    }

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

        self::$setUp = false;

        parent::setUpBeforeClass();
    }

    /**
     * Removes the temp dir
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        self::removeRecursive( self::$tmpDir );
        parent::tearDownAfterClass();
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
        $config = include __DIR__ . "/../../../../../config.php";
        $installDir = $config["install_dir"];

        $containerBuilder = new ContainerBuilder();
        $settingsPath = $installDir . "/eZ/Publish/Core/settings/";
        $loader = new YamlFileLoader( $containerBuilder, new FileLocator( $settingsPath ) );

        $loader->load( 'fieldtypes.yml' );
        $loader->load( 'io.yml' );
        $loader->load( 'repository.yml' );
        $loader->load( 'fieldtype_external_storages.yml' );
        $loader->load( 'storage_engines/common.yml' );
        $loader->load( 'storage_engines/shortcuts.yml' );
        $loader->load( 'storage_engines/legacy.yml' );
        $loader->load( 'search_engines/legacy.yml' );
        $loader->load( 'storage_engines/cache.yml' );
        $loader->load( 'settings.yml' );
        $loader->load( 'fieldtype_services.yml' );
        $loader->load( 'utils.yml' );

        $containerBuilder->setParameter( "ezpublish.kernel.root_dir", $installDir );

        $containerBuilder->setParameter(
            "legacy_dsn",
            $this->getDsn()
        );
        $containerBuilder->setParameter(
            "io_root_dir",
            self::$tmpDir . '/var/ezdemo_site/storage'
        );

        $containerBuilder->compile();

        return $containerBuilder;
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
        return ( self::$tmpDir ? self::$tmpDir . '/' : '' ) . self::$container->getParameter( 'storage_dir' );
    }

    protected function getFilesize( $binaryFileId )
    {
        return filesize( $this->getPathFromId( $binaryFileId ) );
    }
}
