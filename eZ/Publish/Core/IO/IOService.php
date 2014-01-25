<?php
/**
 * File containing the eZ\Publish\Core\Repository\DomainLogic\IOService class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO;

use eZ\Publish\Core\IO\Handler;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFile as SPIBinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct as SPIBinaryFileCreateStruct;
use eZ\Publish\SPI\IO\MimeTypeDetector;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\IO\MetadataHandler;

/**
 * The io service for managing binary files
 *
 * @package eZ\Publish\Core\Repository\DomainLogic
 */
class IOService
{
    /**
     * @var \eZ\Publish\Core\IO\Handler
     */
    protected $ioHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var MimeTypeDetector
     */
    protected $mimeTypeDetector;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\Core\IO\Handler $handler
     * @param array $settings
     */
    public function __construct( Handler $handler, MimeTypeDetector $mimeTypeDetector, array $settings = array() )
    {
        $this->ioHandler = $handler;
        $this->mimeTypeDetector = $mimeTypeDetector;

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
        $binaryCreateStruct->size = $uploadedFile['size'];
        $binaryCreateStruct->inputStream = $fileHandle;
        $binaryCreateStruct->mimeType = $uploadedFile['type'];

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
                'inputStream' => $fileHandle,
                'mimeType' => $this->mimeTypeDetector->getFromPath( $localFile )
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
        if ( empty( $binaryFileCreateStruct->id ) || !is_string( $binaryFileCreateStruct->id ) )
            throw new InvalidArgumentValue( "id", $binaryFileCreateStruct->id, "BinaryFileCreateStruct" );

        if ( !is_int( $binaryFileCreateStruct->size ) || $binaryFileCreateStruct->size < 0 )
            throw new InvalidArgumentValue( "size", $binaryFileCreateStruct->size, "BinaryFileCreateStruct" );

        if ( !is_resource( $binaryFileCreateStruct->inputStream ) )
            throw new InvalidArgumentValue( "inputStream", "property is not a file resource", "BinaryFileCreateStruct" );

        if ( !isset( $binaryFileCreateStruct->mimeType ) )
        {
            $buffer = fread( $binaryFileCreateStruct->inputStream, $binaryFileCreateStruct->size );
            $binaryFileCreateStruct->mimeType = $this->mimeTypeDetector->getFromBuffer( $buffer );
            unset( $buffer );
        }

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
        if ( empty( $binaryFile->id ) || !is_string( $binaryFile->id ) )
            throw new InvalidArgumentValue( "binaryFileId", $binaryFile->id, "BinaryFile" );

        $this->ioHandler->delete( $this->getPrefixedUri( $binaryFile->id ) );
    }

    /**
     * Loads the binary file with $id
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue If the id is invalid
     * @param string $binaryFileId
     * @return BinaryFile|bool the file, or false if it doesn't exist
     */
    public function loadBinaryFile( $binaryFileId )
    {
        if ( empty( $binaryFileId ) || !is_string( $binaryFileId ) )
            throw new InvalidArgumentValue( "binaryFileId", $binaryFileId );

        // @todo An absolute path can in no case be loaded, but throwing an exception is a bit too much at this stage
        if ( $binaryFileId[0] === '/' )
            return false;

        return $this->buildDomainBinaryFileObject(
            $this->ioHandler->load( $this->getPrefixedUri( $binaryFileId ) )
        );
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
        if ( empty( $binaryFile->id ) || !is_string( $binaryFile->id ) )
            throw new InvalidArgumentValue( "binaryFileId", $binaryFile->id, "BinaryFile" );

        return $this->ioHandler->getFileResource(
            $this->getPrefixedUri( $binaryFile->id )
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
        if ( empty( $binaryFile->id ) || !is_string( $binaryFile->id ) )
            throw new InvalidArgumentValue( "binaryFileId", $binaryFile->id, "BinaryFile" );

        return $this->ioHandler->getFileContents(
            $this->getPrefixedUri( $binaryFile->id )
        );
    }

    /**
     * Returns the internal, handler level path to $externalPath
     * @param string $externalId
     * @return string
     */
    public function getInternalPath( $externalId )
    {
        $path = $this->ioHandler->getInternalPath(
            $this->getPrefixedUri( $externalId )
        );
        return $path;
    }

    /**
     * Returns the external path to $internalPath
     * @param string $internalId
     * @return string
     */
    public function getExternalPath( $internalId )
    {
        return $this->removeUriPrefix( $this->ioHandler->getExternalPath( $internalId ) );
    }

    /**
     * Returns the public HTTP uri for $path
     * @param string $path
     * @return string
     */
    public function getUri( $id )
    {
        return $this->ioHandler->getUri( $id );
    }

    /**
     * @param MetadataHandler $metadataHandler
     * @param BinaryFile      $binaryFile
     *
     * @return array
     */
    public function getMetadata( MetadataHandler $metadataHandler, BinaryFile $binaryFile )
    {
        return $this->ioHandler->getMetadata(
            $metadataHandler,
            $this->getPrefixedUri( $binaryFile->id )
        );
    }

    public function exists( $binaryFileId )
    {
        return $this->ioHandler->exists( $this->getPrefixedUri( $binaryFileId ) );
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

        $spiBinaryCreateStruct->id = $this->getPrefixedUri( $binaryFileCreateStruct->id );
        $spiBinaryCreateStruct->size = $binaryFileCreateStruct->size;
        $spiBinaryCreateStruct->setInputStream( $binaryFileCreateStruct->inputStream );
        $spiBinaryCreateStruct->mimeType = $binaryFileCreateStruct->mimeType;

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
        if ( isset( $spiBinaryFile->mimeType ) )
        {
            $mimeType = $spiBinaryFile->mimeType;
        }
        else
        {
            $mimeType = $this->mimeTypeDetector->getFromBuffer(
                $this->ioHandler->getFileContents( $spiBinaryFile->id )
            );
        }

        return new BinaryFile(
            array(
                'size' => (int)$spiBinaryFile->size,
                'mtime' => $spiBinaryFile->mtime,
                'id' => $this->removeUriPrefix( $spiBinaryFile->id ),
                'mimeType' => $mimeType,
                'uri' => $spiBinaryFile->uri
            )
        );
    }

    /**
     * Returns $uri prefixed with what is configured in the service
     * @param string $binaryFileId
     * @return string
     */
    protected function getPrefixedUri( $binaryFileId )
    {
        $prefix = '';
        if ( isset( $this->settings['prefix'] ) )
            $prefix = $this->settings['prefix'] . '/';
        return $prefix . $binaryFileId;
    }

    /**
     * @param mixed $spiBinaryFileId
     * @return string
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    protected function removeUriPrefix( $spiBinaryFileId )
    {
        if ( !isset( $this->settings['prefix'] ) )
        {
            return $spiBinaryFileId;
        }

        if ( strpos( $spiBinaryFileId, $this->settings['prefix'] . '/' ) !== 0 )
        {
            throw new InvalidArgumentException( '$id', "Prefix {$this->settings['prefix']} not found in {$spiBinaryFileId}" );
        }

        $spiBinaryFileId = substr( $spiBinaryFileId, strlen( $this->settings['prefix'] ) + 1 );
        return $spiBinaryFileId;
    }
}
