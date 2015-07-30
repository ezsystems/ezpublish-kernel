<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO;

use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;

/**
 * Interface for Input/Output handling of binary files.
 */
interface IOServiceInterface
{
    /**
     * The the internal prefix added by the IO Service.
     *
     * @param string $prefix
     */
    public function setPrefix($prefix);

    /**
     * Returns the external path to $internalPath.
     *
     * @param string $internalId
     *
     * @deprecated Since 5.4. Use loadBinaryFileByUri.
     *
     * @return string
     */
    public function getExternalPath($internalId);

    /**
     * Creates a BinaryFileCreateStruct object from $localFile.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException When given a non existing / unreadable file
     *
     * @param string $localFile Path to local file
     *
     * @return BinaryFileCreateStruct
     */
    public function newBinaryCreateStructFromLocalFile($localFile);

    /**
     * Checks if a Binary File with $binaryFileId exists.
     *
     * @param string $binaryFileId
     *
     * @return bool
     */
    public function exists($binaryFileId);

    /**
     * Returns the internal, handler level path to $externalPath.
     *
     * @param string $externalId
     *
     * @deprecated Since 5.4. Use the uri property.
     *
     * @return string
     */
    public function getInternalPath($externalId);

    /**
     * Loads the binary file with $id.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue If the id is invalid
     *
     * @param string $binaryFileId
     *
     * @return BinaryFile the file, or false if it doesn't exist
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If no file identified by $binaryFileId exists
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue If $binaryFileId is invalid
     */
    public function loadBinaryFile($binaryFileId);

    /**
     * Loads the binary file with uri $uri.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue If the id is invalid
     *
     * @param string $binaryFileUri
     *
     * @return BinaryFile the file, or false if it doesn't exist
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If no file identified by $binaryFileId exists
     */
    public function loadBinaryFileByUri($binaryFileUri);

    /**
     * Returns the content of the binary file.
     *
     * @param BinaryFile $binaryFile
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If $binaryFile isn't found
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     *
     * @return string
     */
    public function getFileContents(BinaryFile $binaryFile);

    /**
     * Creates a binary file in the repository.
     *
     * @param BinaryFileCreateStruct $binaryFileCreateStruct
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     *
     * @return BinaryFile The created BinaryFile object
     */
    public function createBinaryFile(BinaryFileCreateStruct $binaryFileCreateStruct);

    /**
     * Returns the public HTTP uri for $binaryFileId.
     *
     * @param string $binaryFileId
     *
     * @return string
     */
    public function getUri($binaryFileId);

    /**
     * Gets the mime-type of the BinaryFile.
     *
     * Example: text/xml
     *
     * @param string $binaryFileId
     *
     * @return string|null
     */
    public function getMimeType($binaryFileId);

    /**
     * Returns a read (mode: rb) file resource to the binary file identified by $path.
     *
     * @param BinaryFile $binaryFile
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     *
     * @return resource
     */
    public function getFileInputStream(BinaryFile $binaryFile);

    /**
     * Deletes the BinaryFile with $id.
     *
     * @param BinaryFile $binaryFile
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     */
    public function deleteBinaryFile(BinaryFile $binaryFile);

    /**
     * Creates a BinaryFileCreateStruct object from the uploaded file $uploadedFile.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException When given an invalid uploaded file
     *
     * @param array $uploadedFile The $_POST hash of an uploaded file
     *
     * @return BinaryFileCreateStruct
     */
    public function newBinaryCreateStructFromUploadedFile(array $uploadedFile);

    /**
     * Deletes a directory.
     *
     * @param string $path
     */
    public function deleteDirectory($path);
}
