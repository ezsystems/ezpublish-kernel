<?php
/**
 * File containing the eZ\Publish\Core\Repository\IOService class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO;

use eZ\Publish\Core\IO\Handler;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFile as SPIBinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct as SPIBinaryFileCreateStruct;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * The io service for managing binary files
 *
 * @package eZ\Publish\Core\Repository
 */
class IOService
{
    /**
     * @var \eZ\Publish\SPI\IO\Handler
     */
    protected $ioHandler;

    /**
     * @var array
     */
    protected $settings;


    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\Core\IO\Handler $handler
     * @param array $settings
     */
    public function __construct( Handler $handler, array $settings = array() )
    {
        $this->ioHandler = $handler;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + array(
            //'defaultSetting' => array(),
        );
    }

    /**
     * Creates a BinaryFileCreateStruct object from the uploaded file $uploadedFile
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException When given an invalid uploaded file
     *
     * @param array $uploadedFile The $_POST hash of an uploaded file
     *
     * @return \eZ\Publish\Core\IO\Values\BinaryFileCreateStruct
     */
    public function newBinaryCreateStructFromUploadedFile( array $uploadedFile )
    {
        if ( !is_string( $uploadedFile['tmp_name'] ) || empty( $uploadedFile['tmp_name'] ) )
            throw new InvalidArgumentException( "uploadedFile", "uploadedFile['tmp_name'] does not exist or has invalid value" );

        if ( !is_uploaded_file( $uploadedFile['tmp_name'] ) || !is_readable( $uploadedFile['tmp_name'] ) )
            throw new InvalidArgumentException( "uploadedFile", "file was not uploaded or is unreadable" );

        $fileHandle = fopen( $uploadedFile['tmp_name'], 'rb' );
        if ( $fileHandle === false )
            throw new InvalidArgumentException( "uploadedFile", "failed to get file resource" );

        $binaryCreateStruct = new BinaryFileCreateStruct();
        $binaryCreateStruct->uri = $uploadedFile['tmp_name'];
        $binaryCreateStruct->size = $uploadedFile['size'];
        $binaryCreateStruct->inputStream = $fileHandle;

        return $binaryCreateStruct;
    }

    /**
     * Creates a BinaryFileCreateStruct object from $localFile
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException When given a non existing / unreadable file
     *
     * @param string $localFile Path to local file
     *
     * @return \eZ\Publish\Core\IO\Values\BinaryFileCreateStruct
     */
    public function newBinaryCreateStructFromLocalFile( $localFile )
    {
        if ( empty( $localFile ) || !is_string( $localFile ) )
            throw new InvalidArgumentException( "localFile", "localFile has an invalid value" );

        if ( !is_file( $localFile ) || !is_readable( $localFile ) )
            throw new InvalidArgumentException( "localFile", "file does not exist or is unreadable: {$localFile}" );

        $fileHandle = fopen( $localFile, 'rb' );
        if ( $fileHandle === false )
            throw new InvalidArgumentException( "localFile", "failed to get file resource" );

        $binaryCreateStruct = new BinaryFileCreateStruct(
            array(
                'size' => filesize( $localFile ),
                'inputStream' => $fileHandle
            )
        );

        return $binaryCreateStruct;
    }

    /**
     * Creates a binary file in the repository
     *
     * @param \eZ\Publish\Core\IO\Values\BinaryFileCreateStruct $binaryFileCreateStruct
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     *
     * @return \eZ\Publish\Core\IO\Values\BinaryFile The created BinaryFile object
     */
    public function createBinaryFile( BinaryFileCreateStruct $binaryFileCreateStruct )
    {
        if ( empty( $binaryFileCreateStruct->uri ) || !is_string( $binaryFileCreateStruct->uri ) )
            throw new InvalidArgumentValue( "uri", $binaryFileCreateStruct->uri, "BinaryFileCreateStruct" );

        if ( !is_int( $binaryFileCreateStruct->size ) || $binaryFileCreateStruct->size < 0 )
            throw new InvalidArgumentValue( "size", $binaryFileCreateStruct->size, "BinaryFileCreateStruct" );

        if ( !is_resource( $binaryFileCreateStruct->inputStream ) )
            throw new InvalidArgumentValue( "inputStream", "property is not a file resource", "BinaryFileCreateStruct" );

        $spiBinaryCreateStruct = $this->buildSPIBinaryFileCreateStructObject( $binaryFileCreateStruct );
        $spiBinaryFile = $this->ioHandler->create( $spiBinaryCreateStruct );

        return $this->buildDomainBinaryFileObject( $spiBinaryFile );
    }

    /**
     * Deletes the BinaryFile with $path
     *
     * @param \eZ\Publish\Core\IO\Values\BinaryFile $binaryFile
     *
     * @throws InvalidArgumentValue
     */
    public function deleteBinaryFile( BinaryFile $binaryFile )
    {
        if ( empty( $binaryFile->uri ) || !is_string( $binaryFile->uri ) )
            throw new InvalidArgumentValue( "uri", $binaryFile->uri, "BinaryFile" );

        $this->ioHandler->delete( $this->getPrefixedUri( $binaryFile->uri ) );
    }

    /**
     * Loads the binary file with $id
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue If the id is invalid
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $uri
     *
     * @return \eZ\Publish\Core\IO\Values\BinaryFile|bool the file, or false if it doesn't exist
     */
    public function loadBinaryFile( $uri )
    {
        if ( empty( $uri ) || !is_string( $uri ) )
            throw new InvalidArgumentValue( "binaryFileId", $uri );

        try
        {
            $spiBinaryFile = $this->ioHandler->load( $this->getPrefixedUri( $uri ) );
        }
        catch ( NotFoundException $e )
        {
            return false;
        }

        return $this->buildDomainBinaryFileObject( $spiBinaryFile );
    }

    /**
     * Returns a read (mode: rb) file resource to the binary file identified by $path
     *
     * @param \eZ\Publish\Core\IO\Values\BinaryFile $binaryFile
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     *
     * @return resource
     */
    public function getFileInputStream( BinaryFile $binaryFile )
    {
        if ( empty( $binaryFile->uri ) || !is_string( $binaryFile->uri ) )
            throw new InvalidArgumentValue( "uri", $binaryFile->uri, "BinaryFile" );

        return $this->ioHandler->getFileResource(
            $this->getPrefixedUri( $binaryFile->uri )
        );
    }

    /**
     * Returns the content of the binary file
     *
     * @param \eZ\Publish\Core\IO\Values\BinaryFile $binaryFile
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue

     * @return string
     */
    public function getFileContents( BinaryFile $binaryFile )
    {
        if ( empty( $binaryFile->uri ) || !is_string( $binaryFile->uri ) )
            throw new InvalidArgumentValue( "uri", $binaryFile->uri, "BinaryFile" );

        return $this->ioHandler->getFileContents(
            $this->getPrefixedUri( $binaryFile->uri )
        );
    }

    /**
     * Generates SPI BinaryFileCreateStruct object from provided API BinaryFileCreateStruct object
     *
     * @param \eZ\Publish\Core\IO\Values\BinaryFileCreateStruct $binaryFileCreateStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFileCreateStruct
     */
    protected function buildSPIBinaryFileCreateStructObject( BinaryFileCreateStruct $binaryFileCreateStruct )
    {
        $spiBinaryCreateStruct = new SPIBinaryFileCreateStruct();

        $spiBinaryCreateStruct->uri = $this->getPrefixedUri( $binaryFileCreateStruct->uri );
        $spiBinaryCreateStruct->size = $binaryFileCreateStruct->size;
        $spiBinaryCreateStruct->setInputStream( $binaryFileCreateStruct->inputStream );

        return $spiBinaryCreateStruct;
    }

    /**
     * Generates API BinaryFile object from provided SPI BinaryFile object
     *
     * @param \eZ\Publish\SPI\IO\BinaryFile $spiBinaryFile
     *
     * @return \eZ\Publish\Core\IO\Values\BinaryFile
     */
    protected function buildDomainBinaryFileObject( SPIBinaryFile $spiBinaryFile )
    {
        return new BinaryFile(
            array(
                'size' => (int)$spiBinaryFile->size,
                'mtime' => $spiBinaryFile->mtime,
                'uri' => $this->removeUriPrefix( $spiBinaryFile->uri ),
            )
        );
    }

    /**
     * Returns $uri prefixed with what is configured in the service
     * @param string $uri
     * @return string
     */
    protected function getPrefixedUri( $uri )
    {
        $prefix = '';
        if ( isset( $this->settings['prefix'] ) )
            $prefix = $this->settings['prefix'] . DIRECTORY_SEPARATOR;
        return $prefix . $uri;
    }

    protected function removeUriPrefix( $uri )
    {
        if ( !isset( $this->settings['prefix'] ) )
        {
            return $uri;
        }

        if ( strpos( $uri, $this->settings['prefix'] . DIRECTORY_SEPARATOR ) !== 0 )
            throw new InvalidArgumentException( uri, "Prefix not found" );

        $uri = substr( $uri, strlen( $this->settings['prefix'] ) + 1 );
        return $uri;
    }
}
