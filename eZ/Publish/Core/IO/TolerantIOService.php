<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException;
use eZ\Publish\Core\IO\Exception\InvalidBinaryAbsolutePathException;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\MissingBinaryFile;
use Psr\Log\LoggerInterface;

/**
 * An extended IOService that tolerates physically missing files.
 *
 * Meant to be used on a "broken" instance where the storage directory isn't in sync with the database.
 *
 * Note that it will still return false when exists() is used.
 */
class TolerantIOService extends IOService
{
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Deletes $binaryFile.
     *
     * @param \eZ\Publish\Core\IO\Values\BinaryFile $binaryFile
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue If the binary file is invalid
     * @throws \eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException If the binary file isn't found
     */
    public function deleteBinaryFile(BinaryFile $binaryFile)
    {
        $this->checkBinaryFileId($binaryFile->id);
        $spiUri = $this->getPrefixedUri($binaryFile->id);

        try {
            $this->metadataHandler->delete($spiUri);
        } catch (BinaryFileNotFoundException $e) {
            $this->logMissingFile($binaryFile->uri);
            $logged = true;
        }

        try {
            $this->binarydataHandler->delete($spiUri);
        } catch (BinaryFileNotFoundException $e) {
            if (!isset($logged)) {
                $this->logMissingFile($binaryFile->uri);
            }
        }
    }

    /**
     * Loads the binary file with $binaryFileId.
     *
     * @param string $binaryFileId
     *
     * @return \eZ\Publish\Core\IO\Values\BinaryFile|\eZ\Publish\Core\IO\Values\MissingBinaryFile
     *
     * @throws \eZ\Publish\Core\IO\Exception\InvalidBinaryAbsolutePathException
     */
    public function loadBinaryFile($binaryFileId)
    {
        $this->checkBinaryFileId($binaryFileId);

        if ($this->isAbsolutePath($binaryFileId)) {
            throw new InvalidBinaryAbsolutePathException($binaryFileId);
        }

        try {
            $spiBinaryFile = $this->metadataHandler->load($this->getPrefixedUri($binaryFileId));
        } catch (BinaryFileNotFoundException $e) {
            $this->logMissingFile($binaryFileId);

            return new MissingBinaryFile([
                'id' => $binaryFileId,
                'uri' => $this->binarydataHandler->getUri($this->getPrefixedUri($binaryFileId)),
            ]);
        }

        if (!isset($spiBinaryFile->uri)) {
            $spiBinaryFile->uri = $this->binarydataHandler->getUri($spiBinaryFile->id);
        }

        return $this->buildDomainBinaryFileObject($spiBinaryFile);
    }

    public function loadBinaryFileByUri($binaryFileUri)
    {
        $binaryFileId = $this->binarydataHandler->getIdFromUri($binaryFileUri);
        try {
            $binaryFileId = $this->removeUriPrefix($binaryFileId);
        } catch (InvalidArgumentException $e) {
            $this->logMissingFile($binaryFileUri);

            return new MissingBinaryFile([
                'id' => $binaryFileId,
                'uri' => $binaryFileUri,
            ]);
        }

        try {
            return $this->loadBinaryFile($binaryFileId);
        } catch (BinaryFileNotFoundException $e) {
            $this->logMissingFile($binaryFileUri);

            return new MissingBinaryFile([
                'id' => $binaryFileId,
                'uri' => $this->binarydataHandler->getUri($this->getPrefixedUri($binaryFileId)),
            ]);
        }
    }

    private function logMissingFile($id)
    {
        if (!isset($this->logger)) {
            return;
        }
        $this->logger->info("BinaryFile with id $id not found");
    }
}
