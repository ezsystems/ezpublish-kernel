<?php
/**
 * File containing the IOServiceStub class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use eZ\Publish\API\Repository\IOService;
use eZ\Publish\API\Repository\Values\IO\BinaryFile;
use eZ\Publish\API\Repository\Values\IO\BinaryFileCreateStruct;

use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\InvalidArgumentExceptionStub;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\NotFoundExceptionStub;

/**
 * Service used to handle io operations.
 *
 * @package eZ\Publish\API\Repository
 */
class IOServiceStub implements IOService
{
    /**
     * @var \eZ\Publish\API\Repository\Values\IO\BinaryFile[]
     */
    private $binary = array();

    /**
     * @var string[]
     */
    private $content = array();

    /**
     * @var int
     */
    private $binaryId = 0;

    /**
     * @var string[]
     */
    private $tempFile = array();

    /**
     * Delete all temporary created files.
     */
    public function __destruct()
    {
        foreach ( $this->tempFile as $tempFile )
        {
            unlink( $tempFile );
        }
    }

    /**
     * Creates a BinaryFileCreateStruct object from the uploaded file $uploadedFile
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException When given an invalid uploaded file
     *
     * @param array $uploadedFile The $_POST hash of an uploaded file
     *
     * @return \eZ\Publish\API\Repository\Values\IO\BinaryFileCreateStruct
     */
    public function newBinaryCreateStructFromUploadedFile( array $uploadedFile )
    {
        if ( false === is_uploaded_file( $uploadedFile['tmp_name'] ) )
        {
            throw new InvalidArgumentExceptionStub;
        }
        if ( false === ( $stream = fopen( $uploadedFile['tmp_name'], 'rb' ) ) )
        {
            throw new InvalidArgumentExceptionStub;
        }

        return new BinaryFileCreateStruct(
            array(
                'mimeType' => $uploadedFile['type'],
                'uri' => 'file://' . realpath( $uploadedFile['tmp_name'] ),
                'originalFileName' => $uploadedFile['name'],
                'size' => filesize( $uploadedFile['tmp_name'] ),
                'inputStream' => $stream
            )
        );
    }

    /**
     * Creates a BinaryFileCreateStruct object from $localFile
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException When given a non existing / unreadable file
     *
     * @param string $localFile Path to local file
     *
     * @return \eZ\Publish\API\Repository\Values\IO\BinaryFileCreateStruct
     */
    public function newBinaryCreateStructFromLocalFile( $localFile )
    {
        if ( false === file_exists( $localFile ) || false === is_readable( $localFile ) )
        {
            throw new InvalidArgumentExceptionStub;
        }
        if ( false === ( $stream = fopen( $localFile, 'rb' ) ) )
        {
            throw new InvalidArgumentExceptionStub;
        }

        return new BinaryFileCreateStruct(
            array(
                'mimeType' => mime_content_type( $localFile ),
                'uri' => 'file://' . realpath( $localFile ),
                'originalFileName' => basename( $localFile ),
                'size' => filesize( $localFile ),
                'inputStream' => $stream
            )
        );
    }

    /**
     * Creates a  binary file in the the repository
     *
     * @param \eZ\Publish\API\Repository\Values\IO\BinaryFileCreateStruct $binaryFileCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\IO\BinaryFile The created BinaryFile object
     */
    public function createBinaryFile( BinaryFileCreateStruct $binaryFileCreateStruct )
    {
        $this->binary[++$this->binaryId] = new BinaryFile(
            array(
                'id' => $this->binaryId,
                'size' => $binaryFileCreateStruct->size,
                'ctime' => time(),
                'mtime' => time(),
                'uri' => $binaryFileCreateStruct->uri,
                'originalFile' => $binaryFileCreateStruct->originalFileName,
                'mimeType' => $binaryFileCreateStruct->mimeType
            )
        );

        $this->content[$this->binaryId] = '';
        while ( false === feof( $binaryFileCreateStruct->inputStream ) )
        {
            $this->content[$this->binaryId] .= fgets( $binaryFileCreateStruct->inputStream );
        }

        // ???
        // fclose( $binaryFileCreateStruct->inputStream );

        return $this->binary[$this->binaryId];
    }

    /**
     * Deletes the BinaryFile with $path
     *
     * @param \eZ\Publish\API\Repository\Values\IO\BinaryFile $binaryFile
     */
    public function deleteBinaryFile( BinaryFile $binaryFile )
    {
        unset( $this->binary[$binaryFile->id] );
    }

    /**
     * Loads the binary file with $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param string $binaryFileid
     *
     * @return \eZ\Publish\API\Repository\Values\IO\BinaryFile
     */
    public function loadBinaryFile( $binaryFileId )
    {
        if ( false === isset( $this->binary[$binaryFileId] ) )
        {
            throw new NotFoundExceptionStub;
        }
        return $this->binary[$binaryFileId];
    }

    /**
     * Returns a read (mode: rb) file resource to the binary file identified by $path
     *
     * @param \eZ\Publish\API\Repository\Values\IO\BinaryFile $binaryFile
     *
     * @return resource
     */
    public function getFileInputStream( BinaryFile $binaryFile )
    {
        // We use a temp file here, because it makes streaming really simple
        $tempFile = tempnam( sys_get_temp_dir(), __CLASS__ );
        file_put_contents( $tempFile, $this->content[$binaryFile->id] );

        $this->tempFile[] = $tempFile;

        return fopen( $tempFile, 'rb' );
    }

    /**
     * Returns the content of the binary file
     *
     * @param \eZ\Publish\API\Repository\Values\IO\BinaryFile $binaryFile
     *
     * @return string
     */
    public function getFileContents( BinaryFile $binaryFile )
    {
        return $this->content[$binaryFile->id];
    }

    /**
     * Internal helper method to emulate a rollback.
     *
     * @access private
     *
     * @internal
     *
     * @return void
     */
    public function rollback()
    {
        $this->binary = array();
        $this->binaryId = 0;
        $this->content = array();
    }
}
