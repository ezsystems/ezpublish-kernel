<?php
/**
 * Repository class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Exception\BadConfiguration,
    ezp\Io\BinaryFile, ezp\Io\BinaryFileUpdateStruct, ezp\Io\BinaryFileCreateStruct, ezp\Io\ContentType,
    DateTime;

/**
 * Repository class
 *
 */
class BinaryRepository
{
    /**
     * Constructs the binary repository, either with the default or with override parameters
     * @param string $defaultBackendOverride Override identifier for the default backend
     * @param array $backendsOverride Override of the backends list
     * @param array $backendsConfigurationOverride
     */
    public function __construct( $defaultBackendOverride = null, $backendsOverride = null, $backendsConfigurationOverride = null )
    {
        if ( $defaultBackendOverride === null && self::$defaultBackendOverride !== null )
        {
            $defaultBackendOverride = self::$defaultBackendOverride;
        }

        $this->configuration = Configuration::getInstance( 'io' );

        $backends = $this->configuration->get( 'backends', 'Backends' );
        if ( $backendsOverride != null )
        {
            $backends = $backendsOverride + $backends;
        }
        if ( self::$backendsOverride !== null )
        {
            $backends = self::$backendsOverride + $backends;
        }

        // all backends are indexed, identifier as key, false as the value
        $this->backends = array_fill_keys( array_values( $backends ), false );

        if ( $defaultBackendOverride !== null )
        {
            $this->defaultBackend = $defaultBackendOverride;
        }
        else
        {
            $this->defaultBackend = $this->configuration->get( 'general', 'DefaultBinaryFileBackend' );
        }

        if ( self::$backendsConfigurationOverride !== null )
        {
            if ( $backendsConfigurationOverride !== null )
            {
                $backendsConfigurationOverride = $backendsConfigurationOverride + self::$backendsConfigurationOverride;
            }
            else
            {
                $backendsConfigurationOverride = self::$backendsConfigurationOverride;
            }
        }
        $this->initBackend( $this->defaultBackend, $backendsConfigurationOverride );
    }

    /**
     * Sets global override options for the BinaryRepository
     * @param string $defaultBackendOverride Override identifier for the default backend
     * @param array $backendsOverride Override of the backends list
     * @param array $backendsConfigurationOverride Override of the backends configuration
     */
    public static function setOverrideOptions( $defaultBackendOverride, $backendsOverride = null, $backendsConfigurationOverride = null )
    {
        self::$defaultBackendOverride = $defaultBackendOverride;
        self::$backendsOverride = $backendsOverride;
        self::$backendsConfigurationOverride = $backendsConfigurationOverride;
    }

    /**
     * Creates a BinaryFile object from the uploaded file $uploadedFile
     * @param array $uploadedFile The _POST hash of an uploaded file
     * @return \ezp\Io\BinaryFile
     * @throws InvalidArgumentValue When given an invalid uploaded file
     */
    public function createFromUploadedFile( array $uploadedFile )
    {
        if ( !isset( $uploadedFile['tmp_name'] ) || !is_uploaded_file( $uploadedFile['tmp_name'] ) )
        {
            throw new InvalidArgumentValue( 'uploadedFile', $uploadedFile );
        }

        $file = new BinaryFile();
        $file->size = $uploadedFile['size'];
        $file->ctime = new DateTime;
        $file->mtime = clone $file->ctime;

        // shall we use fileinfo here instead, so that we don't rely on browser provided informations ?
        $file->contentType = $uploadedFile['type'];

        return $file;
    }

    /**
     * Creates a BinaryFile object from $localFile
     * @param string $localFile
     * @return \ezp\Io\BinaryFile
     * @throws InvalidArgumentValue When given a non existing / unreadable file
     */
    public function createFromLocalFile( $localFile )
    {
        if ( !file_exists( $localFile ) || !is_readable( $localFile ) )
        {
            throw new InvalidArgumentValue( 'localFile', $localFile );
        }

        $file = new BinaryFileCreateStruct();
        $file->originalFile = basename( $localFile );
        $file->size = filesize( $localFile );
        $file->ctime = new DateTime;
        $file->mtime = clone $file->ctime;
        $file->contentType = ContentType::getFromPath( $localFile );

        $inputStream = fopen( $localFile, 'rb' );
        $file->setInputStream( $inputStream );
        return $file;
    }

    /**
     * Stores $binaryFile to the repository
     * @param BinaryFileCreateStruct $binaryFile
     * @return BinaryFile The created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $binaryFile )
    {
        return $this->getBackend( $binaryFile->path )->create( $binaryFile );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @param string $path
     * @param BinaryFileUpdateStruct $updateFile
     * @return BinaryFile The update BinaryFile
     */
    public function update( $path, BinaryFileUpdateStruct $updateFile )
    {
        return $this->getBackend( $path )->update( $path, $updateFile );
    }

    /**
     * Checks if a BinaryFile with $path exists in the repository
     *
     * @param mixed $binaryFile
     * @return bool
     */
    public function exists( $path )
    {
        return $this->getBackend( $path )->exists( $path );
    }

    /**
     * Deletes the BinaryFile with $path
     *
     * @param string $path
     * @throws Exception if no such file exists
     */
    public function delete( $path )
    {
        $this->getBackend( $path )->delete( $path );
    }

    /**
     * Loads the binary file with $path
     *
     * @param string $path
     * @return \ezp\Io\BinaryFile
     */
    public function load( $path )
    {
        return $this->getBackend( $path )->load( $path );
    }

    /**
      * Returns a read (mode: rb) file resource to the binary file identified by $path
      * @param string $path
      * @return resource
      */
    public function getFileResource( $path )
    {
        return $this->getBackend( $path )->getFileResource( $path );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     * @param string $path
     * @return string Binary content
     */
    public function getFileContents( $path )
    {
        return $this->getBackend( $path )->getFileContents( $path );
    }

    /**
     * Returns the appropriate backend for $path
     * @param string $path
     * @return \ezp\Io\BinaryStorage\Backend
     */
    private function getBackend( $path )
    {
        // @todo Load appropriate backend based on Match criteria
        return $this->backends[$this->defaultBackend];
    }

    /**
     * Initializes the backend identified  by $identifier
     * @var string $identifier
     * @throws BadConfiguration on non existing backend identifier
     * @throws BadConfiguration on non existing backend class
     */
    private function initBackend( $identifier, $backendsConfigurationOverride = null )
    {
        if ( !array_key_exists( $identifier, $this->backends ) )
        {
            throw new BadConfiguration( "io/general/DefaultBinaryFileBackend" );
        }

        $configurationKey = "backend_settings_{$identifier}";
        if ( isset( $backendsConfigurationOverride[$configurationKey] ) )
        {
            $backendClass = $backendsConfigurationOverride[$configurationKey]["Class"];
        }
        else
        {
            $backendClass = $this->configuration->get( "backend_settings_{$identifier}", "Class" );
        }
        if ( !class_exists( $backendClass ) )
        {
            throw new BadConfiguration(
                "io/backend_settings_{$identifier}/Class",
                "The configured backend class couldn't be found"
            );
        }
        $this->backends[$identifier] = new $backendClass;
    }

    /**
     * BinaryStorage backends instances
     * Uninstanciated ones have false as a value
     * @var \ezp\Io\BinaryStorage\Backend[]
     */
    private $backends = array();

    /**
     * Default BinaryStorage backend identifier
     * @var string
     */
    private $defaultBackend;

    /**
     * Default backend override value
     * @var string
     */
    private static $defaultBackendOverride;

    /**
     * Backends list override value
     * @var array
     */
    private static $backendsOverride;

    /**
     * Bbackends configuration override value
     * @var array
     */
    private static $backendsConfigurationOverride;
}
