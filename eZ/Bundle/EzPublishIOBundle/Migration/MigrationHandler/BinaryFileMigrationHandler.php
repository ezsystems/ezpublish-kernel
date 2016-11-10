<?php

/**
 * File containing the BinaryFileMigrationHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandler;

use eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandler;
use eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException;
use eZ\Publish\SPI\IO\BinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;

class BinaryFileMigrationHandler extends MigrationHandler
{
    public function countFiles() //TODO move to parent?
    {
        return $this->fromMetadataHandler->count($this->scope);
    }

    public function loadMetadataList($limit = null, $offset = null) //TODO move to parent?
    {
        return $this->fromMetadataHandler->loadList($this->scope, $limit, $offset);
    }

    public function migrateFile(BinaryFile $binaryFile)
    {
        try {
            $binaryFileResource = $this->fromBinarydataHandler->getResource($binaryFile->id);
        } catch (BinaryFileNotFoundException $e) {
            //TODO log

            return false;
        }

        $binaryFileCreateStruct = new BinaryFileCreateStruct();
        $binaryFileCreateStruct->id = $binaryFile->id;
        $binaryFileCreateStruct->setInputStream($binaryFileResource);

        try {
            $this->toBinarydataHandler->create($binaryFileCreateStruct);
        } catch (\RuntimeException $e) {
            //TODO log

            return false;
        }

        $metadataCreateStruct = new BinaryFileCreateStruct();
        $metadataCreateStruct->id = $binaryFile->id;
        $metadataCreateStruct->size = $binaryFile->size;
        $metadataCreateStruct->mtime = $binaryFile->mtime;
        $metadataCreateStruct->mimeType = $this->fromMetadataHandler->getMimeType($binaryFile->id);

        try {
            $this->toMetadataHandler->create($metadataCreateStruct);
        } catch (\RuntimeException $e) {
            //TODO log

            return false;
        }

        return true;
    }
}
