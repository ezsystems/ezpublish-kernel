<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO;

use eZ\Publish\SPI\IO\BinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;

/**
 * Provides reading & writing of files meta data (size, modification time...).
 */
interface IOMetadataHandler
{
    /**
     * Stores the file from $binaryFileCreateStruct.
     *
     * @param BinaryFileCreateStruct $spiBinaryFileCreateStruct
     *
     * @return BinaryFile
     *
     * @throws \RuntimeException if an error occured creating the file
     */
    public function create(BinaryFileCreateStruct $spiBinaryFileCreateStruct);

    /**
     * Deletes file $path.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $path is not found
     *
     * @param string $path
     */
    public function delete($spiBinaryFileId);

    /**
     * Loads and returns metadata for $path.
     *
     * @param string $path
     *
     * @return BinaryFile
     */
    public function load($spiBinaryFileId);

    /**
     * Checks if a file $path exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists($spiBinaryFileId);

    /**
     * Returns the file's mimetype. Example: text/plain.
     *
     * @param $spiBinaryFileId
     *
     * @return string
     */
    public function getMimeType($spiBinaryFileId);

    public function deleteDirectory($spiPath);
}
