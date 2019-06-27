<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\IOBinarydataHandler;

use eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException;
use eZ\Publish\Core\IO\IOBinarydataHandler;
use eZ\Publish\Core\IO\UrlDecorator;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException as FlysystemNotFoundException;
use League\Flysystem\FilesystemInterface;

class Flysystem implements IOBinaryDataHandler
{
    /** @var \League\Flysystem\FilesystemInterface */
    private $filesystem;

    /** @var \eZ\Publish\Core\IO\UrlDecorator */
    private $urlDecorator;

    public function __construct(FilesystemInterface $filesystem, UrlDecorator $urlDecorator = null)
    {
        $this->filesystem = $filesystem;
        $this->urlDecorator = $urlDecorator;
    }

    public function create(BinaryFileCreateStruct $binaryFileCreateStruct)
    {
        try {
            $this->filesystem->writeStream(
                $binaryFileCreateStruct->id,
                $binaryFileCreateStruct->getInputStream(),
                [
                    'mimetype' => $binaryFileCreateStruct->mimeType,
                    'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
                ]
            );
        } catch (FileExistsException $e) {
            $this->filesystem->updateStream(
                $binaryFileCreateStruct->id,
                $binaryFileCreateStruct->getInputStream(),
                [
                    'mimetype' => $binaryFileCreateStruct->mimeType,
                    'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
                ]
            );
        }
    }

    public function delete($spiBinaryFileId)
    {
        try {
            $this->filesystem->delete($spiBinaryFileId);
        } catch (FlysystemNotFoundException $e) {
            throw new BinaryFileNotFoundException($spiBinaryFileId, $e);
        }
    }

    public function getContents($spiBinaryFileId)
    {
        try {
            return $this->filesystem->read($spiBinaryFileId);
        } catch (FlysystemNotFoundException $e) {
            throw new BinaryFileNotFoundException($spiBinaryFileId, $e);
        }
    }

    public function getResource($spiBinaryFileId)
    {
        try {
            return $this->filesystem->readStream($spiBinaryFileId);
        } catch (FlysystemNotFoundException $e) {
            throw new BinaryFileNotFoundException($spiBinaryFileId, $e);
        }
    }

    public function getUri($spiBinaryFileId)
    {
        if (isset($this->urlDecorator)) {
            return $this->urlDecorator->decorate($spiBinaryFileId);
        } else {
            return '/' . $spiBinaryFileId;
        }
    }

    public function getIdFromUri($binaryFileUri)
    {
        if (isset($this->urlDecorator)) {
            return $this->urlDecorator->undecorate($binaryFileUri);
        } else {
            return ltrim($binaryFileUri, '/');
        }
    }

    public function deleteDirectory($spiPath)
    {
        $this->filesystem->deleteDir($spiPath);
    }
}
