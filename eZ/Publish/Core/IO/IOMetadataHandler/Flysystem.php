<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\IOMetadataHandler;

use DateTime;
use eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException;
use eZ\Publish\Core\IO\IOMetadataHandler;
use eZ\Publish\SPI\IO\BinaryFile as SPIBinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct as SPIBinaryFileCreateStruct;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;

class Flysystem implements IOMetadataHandler
{
    /** @var FilesystemInterface */
    private $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Only reads & return metadata, since the binarydata handler took care of creating the file already.
     *
     * @throws BinaryFileNotFoundException
     */
    public function create(SPIBinaryFileCreateStruct $spiBinaryFileCreateStruct)
    {
        return $this->load($spiBinaryFileCreateStruct->id);
    }

    /**
     * Does really nothing, the binary data handler takes care of it.
     *
     * @param $spiBinaryFileId
     */
    public function delete($spiBinaryFileId)
    {
    }

    public function load($spiBinaryFileId)
    {
        try {
            $info = $this->filesystem->getMetadata($spiBinaryFileId);
        } catch (FileNotFoundException $e) {
            throw new BinaryFileNotFoundException($spiBinaryFileId);
        }

        return $this->getSPIBinaryForMetadata($info, $spiBinaryFileId);
    }

    public function loadList($limit = null, $offset = null)
    {
        $metadataList = $this->getMetadataListWithoutDirectories();
        $offset = $offset === null ? 0 : $offset;
        $limit = $limit === null ? count($metadataList) : $offset + $limit;
        $limit = $limit > count($metadataList) ? count($metadataList) : $limit;
        $spiBinaryFileList = [];

        for ($i = $offset; $i < $limit; ++$i) {
            $spiBinaryFileList[] = $this->getSPIBinaryForMetadata($metadataList[$i]);
        }

        return $spiBinaryFileList;
    }

    public function exists($spiBinaryFileId)
    {
        return $this->filesystem->has($spiBinaryFileId);
    }

    public function getMimeType($spiBinaryFileId)
    {
        return $this->filesystem->getMimetype($spiBinaryFileId);
    }

    /**
     * Does nothing, as the binarydata handler takes care of it.
     */
    public function deleteDirectory($spiPath)
    {
    }

    public function count()
    {
        return count($this->getMetadataListWithoutDirectories());
    }

    /**
     * Return the metadata of all entries except directories.
     *
     * @return array
     */
    private function getMetadataListWithoutDirectories()
    {
        $metadataList = $this->filesystem->listContents('', true);

        $filteredMetadataList = [];
        foreach ($metadataList as $metadata) {
            if (array_key_exists('size', $metadata)) {
                $filteredMetadataList[] = $metadata;
            }
        }

        return $filteredMetadataList;
    }

    private function getSPIBinaryForMetadata($metadata, $spiBinaryFileId = null)
    {
        $spiBinaryFile = new SPIBinaryFile();
        $spiBinaryFile->id = $spiBinaryFileId ?: $metadata['path'];
        $spiBinaryFile->size = $metadata['size'];

        if (isset($metadata['timestamp'])) {
            $spiBinaryFile->mtime = new DateTime('@' . $metadata['timestamp']);
        }

        return $spiBinaryFile;
    }
}
