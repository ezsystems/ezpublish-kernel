<?php

/**
 * File containing the BinaryBaseStorage class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\BinaryBase;

use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\SPI\FieldType\BinaryBase\PathGenerator;
use eZ\Publish\SPI\IO\MimeTypeDetector;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * Storage for binary files.
 *
 * @method \eZ\Publish\Core\FieldType\BinaryBase\BinaryBaseStorage\Gateway getGateway(array $context)
 */
class BinaryBaseStorage extends GatewayBasedStorage
{
    /**
     * An instance of IOService configured to store to the images folder.
     *
     * @var \eZ\Publish\Core\IO\IOServiceInterface
     */
    protected $IOService;

    /** @var \eZ\Publish\SPI\FieldType\BinaryBase\PathGenerator */
    protected $pathGenerator;

    /** @var \eZ\Publish\SPI\IO\MimeTypeDetector */
    protected $mimeTypeDetector;

    /** @var PathGenerator */
    protected $downloadUrlGenerator;

    /**
     * Construct from gateways.
     *
     * @param \eZ\Publish\Core\FieldType\StorageGateway[] $gateways
     * @param \eZ\Publish\Core\IO\IOServiceInterface $IOService
     * @param \eZ\Publish\SPI\FieldType\BinaryBase\PathGenerator $pathGenerator
     * @param \eZ\Publish\SPI\IO\MimeTypeDetector $mimeTypeDetector
     */
    public function __construct(array $gateways, IOServiceInterface $IOService, PathGenerator $pathGenerator, MimeTypeDetector $mimeTypeDetector)
    {
        parent::__construct($gateways);
        $this->IOService = $IOService;
        $this->pathGenerator = $pathGenerator;
        $this->mimeTypeDetector = $mimeTypeDetector;
    }

    /**
     * @param PathGenerator $downloadUrlGenerator
     */
    public function setDownloadUrlGenerator(PathGenerator $downloadUrlGenerator)
    {
        $this->downloadUrlGenerator = $downloadUrlGenerator;
    }

    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        if ($field->value->externalData === null) {
            $this->deleteFieldData($versionInfo, [$field->id], $context);

            return false;
        }

        if (isset($field->value->externalData['inputUri'])) {
            $field->value->externalData['mimeType'] = $this->mimeTypeDetector->getFromPath($field->value->externalData['inputUri']);
            $createStruct = $this->IOService->newBinaryCreateStructFromLocalFile($field->value->externalData['inputUri']);
            $createStruct->id = $this->pathGenerator->getStoragePathForField($field, $versionInfo);
            $binaryFile = $this->IOService->createBinaryFile($createStruct);

            $field->value->externalData['id'] = $binaryFile->id;
            $field->value->externalData['mimeType'] = $createStruct->mimeType;
            $field->value->externalData['uri'] = isset($this->downloadUrlGenerator) ?
                $this->downloadUrlGenerator->getStoragePathForField($field, $versionInfo) :
                $binaryFile->uri;
        }

        $this->removeOldFile($field->id, $versionInfo->versionNo, $context);

        $this->getGateway($context)->storeFileReference($versionInfo, $field);
    }

    public function copyLegacyField(VersionInfo $versionInfo, Field $field, Field $originalField, array $context)
    {
        if ($originalField->value->externalData === null) {
            return false;
        }

        // field translations have their own file reference, but to the original file
        $originalField->value->externalData['id'];

        return $this->getGateway($context)->storeFileReference($versionInfo, $field);
    }

    /**
     * Removes the old file referenced by $fieldId in $versionNo, if not
     * referenced else where.
     *
     * @param mixed $fieldId
     * @param string $versionNo
     * @param array $context
     */
    protected function removeOldFile($fieldId, $versionNo, array $context)
    {
        $gateway = $this->getGateway($context);

        $fileReference = $gateway->getFileReferenceData($fieldId, $versionNo);
        if ($fileReference === null) {
            // No previous file
            return;
        }

        $gateway->removeFileReference($fieldId, $versionNo);

        $fileCounts = $gateway->countFileReferences(array($fileReference['id']));

        if ($fileCounts[$fileReference['id']] === 0) {
            $binaryFile = $this->IOService->loadBinaryFile($fileReference['id']);
            $this->IOService->deleteBinaryFile($binaryFile);
        }
    }

    public function getFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        $field->value->externalData = $this->getGateway($context)->getFileReferenceData($field->id, $versionInfo->versionNo);
        if ($field->value->externalData !== null) {
            $binaryFile = $this->IOService->loadBinaryFile($field->value->externalData['id']);
            $field->value->externalData['fileSize'] = $binaryFile->size;
            $field->value->externalData['uri'] = isset($this->downloadUrlGenerator) ?
                $this->downloadUrlGenerator->getStoragePathForField($field, $versionInfo) :
                $binaryFile->uri;
        }
    }

    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context)
    {
        if (empty($fieldIds)) {
            return;
        }

        $gateway = $this->getGateway($context);

        $referencedFiles = $gateway->getReferencedFiles($fieldIds, $versionInfo->versionNo);

        $gateway->removeFileReferences($fieldIds, $versionInfo->versionNo);

        $referenceCountMap = $gateway->countFileReferences($referencedFiles);

        foreach ($referenceCountMap as $filePath => $count) {
            if ($count === 0) {
                $binaryFile = $this->IOService->loadBinaryFile($filePath);
                $this->IOService->deleteBinaryFile($binaryFile);
            }
        }
    }

    public function hasFieldData()
    {
        return true;
    }

    public function getIndexData(VersionInfo $versionInfo, Field $field, array $context)
    {
    }
}
