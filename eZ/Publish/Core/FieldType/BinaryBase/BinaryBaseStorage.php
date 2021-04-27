<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\BinaryBase;

use eZ\Publish\SPI\FieldType\BinaryBase\RouteAwarePathGenerator;
use eZ\Publish\SPI\FieldType\GatewayBasedStorage;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\SPI\FieldType\BinaryBase\PathGenerator;
use eZ\Publish\SPI\FieldType\StorageGateway;
use eZ\Publish\SPI\IO\MimeTypeDetector;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * Storage for binary files.
 */
class BinaryBaseStorage extends GatewayBasedStorage
{
    /**
     * An instance of IOService configured to store to the images folder.
     *
     * @var \eZ\Publish\Core\IO\IOServiceInterface
     */
    protected $ioService;

    /** @var \eZ\Publish\SPI\FieldType\BinaryBase\PathGenerator */
    protected $pathGenerator;

    /** @var \eZ\Publish\SPI\IO\MimeTypeDetector */
    protected $mimeTypeDetector;

    /** @var \eZ\Publish\SPI\FieldType\BinaryBase\PathGenerator */
    protected $downloadUrlGenerator;

    /** @var \eZ\Publish\Core\FieldType\BinaryBase\BinaryBaseStorage\Gateway */
    protected $gateway;

    /**
     * Construct from gateways.
     *
     * @param \eZ\Publish\SPI\FieldType\StorageGateway $gateway
     * @param \eZ\Publish\Core\IO\IOServiceInterface $ioService
     * @param \eZ\Publish\SPI\FieldType\BinaryBase\PathGenerator $pathGenerator
     * @param \eZ\Publish\SPI\IO\MimeTypeDetector $mimeTypeDetector
     */
    public function __construct(
        StorageGateway $gateway,
        IOServiceInterface $ioService,
        PathGenerator $pathGenerator,
        MimeTypeDetector $mimeTypeDetector
    ) {
        parent::__construct($gateway);
        $this->ioService = $ioService;
        $this->pathGenerator = $pathGenerator;
        $this->mimeTypeDetector = $mimeTypeDetector;
    }

    /**
     * @param \eZ\Publish\SPI\FieldType\BinaryBase\PathGenerator $downloadUrlGenerator
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
            $createStruct = $this->ioService->newBinaryCreateStructFromLocalFile($field->value->externalData['inputUri']);
            $createStruct->id = $this->pathGenerator->getStoragePathForField($field, $versionInfo);
            $binaryFile = $this->ioService->createBinaryFile($createStruct);

            $field->value->externalData['id'] = $binaryFile->id;
            $field->value->externalData['mimeType'] = $createStruct->mimeType;
            $field->value->externalData['uri'] = isset($this->downloadUrlGenerator) ?
                $this->downloadUrlGenerator->getStoragePathForField($field, $versionInfo) :
                $binaryFile->uri;
        }

        // copy from another field
        if (!isset($field->value->externalData['mimeType']) && isset($field->value->externalData['id'])) {
            $field->value->externalData['mimeType'] = $this->ioService->getMimeType($field->value->externalData['id']);
        }

        $referenced = $this->gateway->getReferencedFiles([$field->id], $versionInfo->versionNo);
        if ($referenced === null || !in_array($field->value->externalData['id'], $referenced)) {
            $this->removeOldFile($field->id, $versionInfo->versionNo, $context);
        }

        $this->gateway->storeFileReference($versionInfo, $field);
    }

    public function copyLegacyField(VersionInfo $versionInfo, Field $field, Field $originalField, array $context)
    {
        if ($originalField->value->externalData === null) {
            return false;
        }

        // field translations have their own file reference, but to the original file
        $originalField->value->externalData['id'];

        return $this->gateway->storeFileReference($versionInfo, $field);
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
        $fileReference = $this->gateway->getFileReferenceData($fieldId, $versionNo);
        if ($fileReference === null) {
            // No previous file
            return;
        }

        $this->gateway->removeFileReference($fieldId, $versionNo);

        $fileCounts = $this->gateway->countFileReferences([$fileReference['id']]);

        if ($fileCounts[$fileReference['id']] === 0) {
            $binaryFile = $this->ioService->loadBinaryFile($fileReference['id']);
            $this->ioService->deleteBinaryFile($binaryFile);
        }
    }

    public function getFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        $field->value->externalData = $this->gateway->getFileReferenceData($field->id, $versionInfo->versionNo);
        if ($field->value->externalData !== null) {
            $binaryFile = $this->ioService->loadBinaryFile($field->value->externalData['id']);
            $field->value->externalData['fileSize'] = $binaryFile->size;

            $uri = $binaryFile->uri;
            if (isset($this->downloadUrlGenerator)) {
                $uri = $this->downloadUrlGenerator->getStoragePathForField($field, $versionInfo);

                if ($this->downloadUrlGenerator instanceof RouteAwarePathGenerator) {
                    $field->value->externalData['route'] = $this->downloadUrlGenerator->getRoute($field, $versionInfo);
                    $field->value->externalData['route_parameters'] = $this->downloadUrlGenerator->getParameters(
                        $field,
                        $versionInfo
                    );
                }
            }

            $field->value->externalData['uri'] = $uri;
        }
    }

    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context)
    {
        if (empty($fieldIds)) {
            return;
        }

        $referencedFiles = $this->gateway->getReferencedFiles($fieldIds, $versionInfo->versionNo);

        $this->gateway->removeFileReferences($fieldIds, $versionInfo->versionNo);

        $referenceCountMap = $this->gateway->countFileReferences($referencedFiles);

        foreach ($referenceCountMap as $filePath => $count) {
            if ($count === 0) {
                $binaryFile = $this->ioService->loadBinaryFile($filePath);
                $this->ioService->deleteBinaryFile($binaryFile);
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
