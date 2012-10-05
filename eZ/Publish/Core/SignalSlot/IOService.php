<?php
/**
 * IOService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot;
use \eZ\Publish\API\Repository\IOService as IOServiceInterface,

/**
 * IOService class
 * @package eZ\Publish\Core\SignalSlot
 */
class IOService implements IOServiceInterface
{
    /**
     * Aggregated service
     *
     * @var \eZ\Publish\API\Repository\IOService
     */
    protected $service;

    /**
     * SignalDispatcher
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\IOService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( IOServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service          = $service;
        $this->signalDispatcher = $signalDispatcher;
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
    public function newBinaryCreateStructFromUploadedFile( $uploadedFile )
    {
        $returnValue = $this->service->newBinaryCreateStructFromUploadedFile( $uploadedFile );
        $this->signalDispatcher()->emit(
            new Signal\IOService\NewBinaryCreateStructFromUploadedFileSignal( array(
                'uploadedFile' => $uploadedFile,
            ) )
        );
        return $returnValue;
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
        $returnValue = $this->service->newBinaryCreateStructFromLocalFile( $localFile );
        $this->signalDispatcher()->emit(
            new Signal\IOService\NewBinaryCreateStructFromLocalFileSignal( array(
                'localFile' => $localFile,
            ) )
        );
        return $returnValue;
    }

    /**
     * Creates a  binary file in the the repository
     *
     * @param \eZ\Publish\API\Repository\Values\IO\BinaryFileCreateStruct $binaryFileCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\IO\BinaryFile The created BinaryFile object
     */
    public function createBinaryFile( eZ\Publish\API\Repository\Values\IO\BinaryFileCreateStruct $binaryFileCreateStruct )
    {
        $returnValue = $this->service->createBinaryFile( $binaryFileCreateStruct );
        $this->signalDispatcher()->emit(
            new Signal\IOService\CreateBinaryFileSignal( array(
                'binaryFileCreateStruct' => $binaryFileCreateStruct,
            ) )
        );
        return $returnValue;
    }

    /**
     * Deletes the BinaryFile with $path
     *
     * @param \eZ\Publish\API\Repository\Values\IO\BinaryFile $binaryFile
     */
    public function deleteBinaryFile( eZ\Publish\API\Repository\Values\IO\BinaryFile $binaryFile )
    {
        $returnValue = $this->service->deleteBinaryFile( $binaryFile );
        $this->signalDispatcher()->emit(
            new Signal\IOService\DeleteBinaryFileSignal( array(
                'binaryFile' => $binaryFile,
            ) )
        );
        return $returnValue;
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
    public function loadBinaryFile( $binaryFileid )
    {
        $returnValue = $this->service->loadBinaryFile( $binaryFileid );
        $this->signalDispatcher()->emit(
            new Signal\IOService\LoadBinaryFileSignal( array(
                'binaryFileid' => $binaryFileid,
            ) )
        );
        return $returnValue;
    }

    /**
     * Returns a read (mode: rb) file resource to the binary file identified by $path
     *
     * @param \eZ\Publish\API\Repository\Values\IO\BinaryFile $binaryFile
     *
     * @return resource
     */
    public function getFileInputStream( eZ\Publish\API\Repository\Values\IO\BinaryFile $binaryFile )
    {
        $returnValue = $this->service->getFileInputStream( $binaryFile );
        $this->signalDispatcher()->emit(
            new Signal\IOService\GetFileInputStreamSignal( array(
                'binaryFile' => $binaryFile,
            ) )
        );
        return $returnValue;
    }

    /**
     * Returns the content of the binary file
     *
     * @param \eZ\Publish\API\Repository\Values\IO\BinaryFile $binaryFile
     *
     * @return string
     */
    public function getFileContents( eZ\Publish\API\Repository\Values\IO\BinaryFile $binaryFile )
    {
        $returnValue = $this->service->getFileContents( $binaryFile );
        $this->signalDispatcher()->emit(
            new Signal\IOService\GetFileContentsSignal( array(
                'binaryFile' => $binaryFile,
            ) )
        );
        return $returnValue;
    }

}

