<?php

/**
 * File containing the IOService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\IOService as APIIOService;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use eZ\Publish\Core\REST\Common\RequestParser;
use eZ\Publish\Core\REST\Common\Input\Dispatcher;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * Service used to handle io operations.
 */
class IOService implements APIIOService, Sessionable
{
    /** @var \eZ\Publish\Core\REST\Client\HttpClient */
    private $client;

    /** @var \eZ\Publish\Core\REST\Common\Input\Dispatcher */
    private $inputDispatcher;

    /** @var \eZ\Publish\Core\REST\Common\Output\Visitor */
    private $outputVisitor;

    /** @var \eZ\Publish\Core\REST\Common\RequestParser */
    private $requestParser;

    /**
     * @param \eZ\Publish\Core\REST\Client\HttpClient $client
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $outputVisitor
     * @param \eZ\Publish\Core\REST\Common\RequestParser $requestParser
     */
    public function __construct(HttpClient $client, Dispatcher $inputDispatcher, Visitor $outputVisitor, RequestParser $requestParser)
    {
        $this->client = $client;
        $this->inputDispatcher = $inputDispatcher;
        $this->outputVisitor = $outputVisitor;
        $this->requestParser = $requestParser;
    }

    /**
     * Set session ID.
     *
     * Only for testing
     *
     * @param mixed tringid
     *
     * @private
     */
    public function setSession($id)
    {
        if ($this->outputVisitor instanceof Sessionable) {
            $this->outputVisitor->setSession($id);
        }
    }

    /**
     * Creates a BinaryFileCreateStruct object from the uploaded file $uploadedFile.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException When given an invalid uploaded file
     *
     * @param array $uploadedFile The $_POST hash of an uploaded file
     *
     * @return \eZ\Publish\Core\IO\Values\BinaryFileCreateStruct
     */
    public function newBinaryCreateStructFromUploadedFile(array $uploadedFile)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Creates a BinaryFileCreateStruct object from $localFile.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException When given a non existing / unreadable file
     *
     * @param string $localFile Path to local file
     *
     * @return \eZ\Publish\Core\IO\Values\BinaryFileCreateStruct
     */
    public function newBinaryCreateStructFromLocalFile($localFile)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Creates a  binary file in the the repository.
     *
     * @param \eZ\Publish\Core\IO\Values\BinaryFileCreateStruct $binaryFileCreateStruct
     *
     * @return \eZ\Publish\Core\IO\Values\BinaryFile The created BinaryFile object
     */
    public function createBinaryFile(BinaryFileCreateStruct $binaryFileCreateStruct)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Deletes the BinaryFile with $path.
     *
     * @param \eZ\Publish\Core\IO\Values\BinaryFile $binaryFile
     */
    public function deleteBinaryFile(BinaryFile $binaryFile)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Loads the binary file with $id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param string $binaryFileid
     *
     * @return \eZ\Publish\Core\IO\Values\BinaryFile
     */
    public function loadBinaryFile($binaryFileId)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Returns a read (mode: rb) file resource to the binary file identified by $path.
     *
     * @param \eZ\Publish\Core\IO\Values\BinaryFile $binaryFile
     *
     * @return resource
     */
    public function getFileInputStream(BinaryFile $binaryFile)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Returns the content of the binary file.
     *
     * @param \eZ\Publish\Core\IO\Values\BinaryFile $binaryFile
     *
     * @return string
     */
    public function getFileContents(BinaryFile $binaryFile)
    {
        throw new \Exception('@todo: Implement.');
    }
}
