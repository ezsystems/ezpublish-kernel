<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\IO;

use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\SPI\MVC\EventSubscriber\ConfigScopeChangeSubscriber;

class ConfigScopeChangeAwareIOService implements IOServiceInterface, ConfigScopeChangeSubscriber
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Publish\Core\IO\IOServiceInterface */
    private $innerIOService;

    /** @var string */
    private $prefixParameterName;

    public function __construct(
        ConfigResolverInterface $configResolver,
        IOServiceInterface $innerIOService,
        string $prefixParameterName
    ) {
        $this->configResolver = $configResolver;
        $this->innerIOService = $innerIOService;
        $this->prefixParameterName = $prefixParameterName;

        // set initial prefix on inner IOService
        $this->setPrefix($this->configResolver->getParameter($this->prefixParameterName));
    }

    public function setPrefix($prefix): void
    {
        $this->innerIOService->setPrefix($prefix);
    }

    public function getExternalPath($internalId): string
    {
        return $this->innerIOService->getExternalPath($internalId);
    }

    public function newBinaryCreateStructFromLocalFile($localFile): BinaryFileCreateStruct
    {
        return $this->innerIOService->newBinaryCreateStructFromLocalFile($localFile);
    }

    public function exists($binaryFileId): bool
    {
        return $this->innerIOService->exists($binaryFileId);
    }

    public function getInternalPath($externalId): string
    {
        return $this->innerIOService->getInternalPath($externalId);
    }

    public function loadBinaryFile($binaryFileId): BinaryFile
    {
        return $this->innerIOService->loadBinaryFile($binaryFileId);
    }

    public function loadBinaryFileByUri($binaryFileUri): BinaryFile
    {
        return $this->innerIOService->loadBinaryFileByUri($binaryFileUri);
    }

    public function getFileContents(BinaryFile $binaryFile): string
    {
        return $this->innerIOService->getFileContents($binaryFile);
    }

    public function createBinaryFile(BinaryFileCreateStruct $binaryFileCreateStruct): BinaryFile
    {
        return $this->innerIOService->createBinaryFile($binaryFileCreateStruct);
    }

    public function getUri($binaryFileId): string
    {
        return $this->innerIOService->getUri($binaryFileId);
    }

    public function getMimeType($binaryFileId): ?string
    {
        return $this->innerIOService->getMimeType($binaryFileId);
    }

    public function getFileInputStream(BinaryFile $binaryFile)
    {
        return $this->innerIOService->getFileInputStream($binaryFile);
    }

    public function deleteBinaryFile(BinaryFile $binaryFile): void
    {
        $this->innerIOService->deleteBinaryFile($binaryFile);
    }

    public function newBinaryCreateStructFromUploadedFile(array $uploadedFile): BinaryFileCreateStruct
    {
        return $this->innerIOService->newBinaryCreateStructFromUploadedFile($uploadedFile);
    }

    public function deleteDirectory($path): void
    {
        $this->innerIOService->deleteDirectory($path);
    }

    public function onConfigScopeChange(SiteAccess $siteAccess): void
    {
        $this->setPrefix($this->configResolver->getParameter($this->prefixParameterName));
    }
}
