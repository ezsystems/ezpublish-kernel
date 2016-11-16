<?php

/**
 * File containing the MigrationHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration;

use eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory;
use eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException;
use eZ\Publish\SPI\IO\BinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;

class MigrationHandler implements MigrationHandlerInterface
{
    /** @var \eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory */
    private $metadataHandlerFactory;

    /** @var \eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory */
    private $binarydataHandlerFactory;

    /** @var \eZ\Publish\Core\IO\IOMetadataHandler */
    protected $fromMetadataHandler;

    /** @var \eZ\Publish\Core\IO\IOBinarydataHandler */
    protected $fromBinarydataHandler;

    /** @var \eZ\Publish\Core\IO\IOMetadataHandler */
    protected $toMetadataHandler;

    /** @var \eZ\Publish\Core\IO\IOBinarydataHandler */
    protected $toBinarydataHandler;

    public function __construct(
        HandlerFactory $metadataHandlerFactory,
        HandlerFactory $binarydataHandlerFactory
    ) {
        $this->metadataHandlerFactory = $metadataHandlerFactory;
        $this->binarydataHandlerFactory = $binarydataHandlerFactory;
    }

    public function setIODataHandlersByIdentifiers(
        $fromMetadataHandlerIdentifier,
        $fromBinarydataHandlerIdentifier,
        $toMetadataHandlerIdentifier,
        $toBinarydataHandlerIdentifier
    ) {
        $this->fromMetadataHandler = $this->metadataHandlerFactory->getConfiguredHandler($fromMetadataHandlerIdentifier);
        $this->fromBinarydataHandler = $this->binarydataHandlerFactory->getConfiguredHandler($fromBinarydataHandlerIdentifier);
        $this->toMetadataHandler = $this->metadataHandlerFactory->getConfiguredHandler($toMetadataHandlerIdentifier);
        $this->toBinarydataHandler = $this->binarydataHandlerFactory->getConfiguredHandler($toBinarydataHandlerIdentifier);

        return $this;
    }

    public function countFiles()
    {
        return $this->fromMetadataHandler->count();
    }

    public function loadMetadataList($limit = null, $offset = null)
    {
        return $this->fromMetadataHandler->loadList($limit, $offset);
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
