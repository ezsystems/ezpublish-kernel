<?php

/**
 * File containing the Legacy class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Image\IO;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use eZ\Publish\Core\IO\Values\MissingBinaryFile;

/**
 * Legacy Image IOService.
 *
 * Acts as a dispatcher between the two IOService instances required by FieldType\Image in Legacy.
 * - One is the usual one, as used in ImageStorage, that uses 'images' as the prefix
 * - The other is a special one, that uses 'images-versioned' as the  prefix, in  order to cope with content created
 *   from the backoffice
 *
 * To load a binary file, this service will first try with the normal IOService,
 * and on exception, will fall back to the draft IOService.
 *
 * In addition, loadBinaryFile() will also hide the need to explicitly call getExternalPath()
 * on  the internal path stored in legacy.
 */
class Legacy implements IOServiceInterface
{
    /**
     * Published images IO Service.
     *
     * @var \eZ\Publish\Core\IO\IOServiceInterface
     */
    private $publishedIOService;

    /**
     * Draft images IO Service.
     *
     * @var \eZ\Publish\Core\IO\IOServiceInterface
     */
    private $draftIOService;

    /**
     * Prefix for published images.
     * Example: var/ezdemo_site/storage/images.
     *
     * @var string
     */
    private $publishedPrefix;

    /**
     * Prefix for draft images.
     * Example: var/ezdemo_site/storage/images-versioned.
     *
     * @var string
     */
    private $draftPrefix;

    /** @var \eZ\Publish\Core\FieldType\Image\IO\OptionsProvider */
    private $optionsProvider;

    /**
     * @param \eZ\Publish\Core\IO\IOServiceInterface $publishedIOService
     * @param \eZ\Publish\Core\IO\IOServiceInterface $draftIOService
     * @param array $options Path options. Known keys: var_dir, storage_dir, draft_images_dir, published_images_dir.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException if required options are missing
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     *         If any of the passed options has not been defined or does not contain an allowed value
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     *         If a required option is missing.
     */
    public function __construct(IOServiceInterface $publishedIOService, IOServiceInterface $draftIOService, OptionsProvider $optionsProvider)
    {
        $this->publishedIOService = $publishedIOService;
        $this->draftIOService = $draftIOService;
        $this->optionsProvider = $optionsProvider;
        $this->setPrefixes();
    }

    /**
     * Sets the IOService prefix.
     */
    public function setPrefix($prefix)
    {
        $this->publishedIOService->setPrefix($prefix);
        $this->draftIOService->setPrefix($prefix);
    }

    /**
     * Computes the paths to published & draft images path using the options from the provider.
     */
    private function setPrefixes()
    {
        $pathArray = [$this->optionsProvider->getVarDir()];

        // The storage dir itself might be null
        if ($storageDir = $this->optionsProvider->getStorageDir()) {
            $pathArray[] = $storageDir;
        }

        $this->draftPrefix = implode('/', array_merge($pathArray, [$this->optionsProvider->getDraftImagesDir()]));
        $this->publishedPrefix = implode('/', array_merge($pathArray, [$this->optionsProvider->getPublishedImagesDir()]));
    }

    public function getExternalPath($internalId)
    {
        return $this->publishedIOService->getExternalPath($internalId);
    }

    public function newBinaryCreateStructFromLocalFile($localFile)
    {
        return $this->publishedIOService->newBinaryCreateStructFromLocalFile($localFile);
    }

    public function exists($binaryFileId)
    {
        return $this->publishedIOService->exists($binaryFileId);
    }

    public function getInternalPath($externalId)
    {
        return $this->publishedIOService->getInternalPath($externalId);
    }

    public function loadBinaryFile($binaryFileId)
    {
        // If the id is an internal (absolute) path to a draft image, use the draft service to get external path & load
        if ($this->isDraftImagePath($binaryFileId)) {
            return $this->draftIOService->loadBinaryFile($this->draftIOService->getExternalPath($binaryFileId));
        }

        // If the id is an internal path (absolute) to a published image, replace with the internal path
        if ($this->isPublishedImagePath($binaryFileId)) {
            $binaryFileId = $this->publishedIOService->getExternalPath($binaryFileId);
        }

        try {
            $image = $this->publishedIOService->loadBinaryFile($binaryFileId);

            if ($image instanceof MissingBinaryFile) {
                throw new InvalidArgumentException('binaryFileId', sprintf("Can't find file with id %s", $binaryFileId));
            }

            return $image;
        } catch (InvalidArgumentException $prefixException) {
            // InvalidArgumentException means that the prefix didn't match, NotFound can pass through
            try {
                return $this->draftIOService->loadBinaryFile($binaryFileId);
            } catch (InvalidArgumentException $e) {
                throw $prefixException;
            }
        }
    }

    /**
     * Since both services should use the same uri, we can use any of them to *GET* the URI.
     */
    public function loadBinaryFileByUri($binaryFileUri)
    {
        try {
            $image = $this->publishedIOService->loadBinaryFileByUri($binaryFileUri);

            if ($image instanceof MissingBinaryFile) {
                throw new InvalidArgumentException('binaryFileUri', sprintf("Can't find file with url %s", $binaryFileUri));
            }

            return $image;
        } catch (InvalidArgumentException $prefixException) {
            // InvalidArgumentException means that the prefix didn't match, NotFound can pass through
            try {
                return $this->draftIOService->loadBinaryFileByUri($binaryFileUri);
            } catch (InvalidArgumentException $e) {
                throw $prefixException;
            }
        }
    }

    public function getFileContents(BinaryFile $binaryFile)
    {
        if ($this->draftIOService->exists($binaryFile->id)) {
            return $this->draftIOService->getFileContents($binaryFile);
        }

        return $this->publishedIOService->getFileContents($binaryFile);
    }

    public function createBinaryFile(BinaryFileCreateStruct $binaryFileCreateStruct)
    {
        return $this->publishedIOService->createBinaryFile($binaryFileCreateStruct);
    }

    public function getUri($binaryFileId)
    {
        return $this->publishedIOService->getUri($binaryFileId);
    }

    public function getMimeType($binaryFileId)
    {
        // If the id is an internal (absolute) path to a draft image, use the draft service to get external path & load
        if ($this->isDraftImagePath($binaryFileId)) {
            return $this->draftIOService->getMimeType($this->draftIOService->getExternalPath($binaryFileId));
        }

        // If the id is an internal path (absolute) to a published image, replace with the internal path
        if ($this->isPublishedImagePath($binaryFileId)) {
            $binaryFileId = $this->publishedIOService->getExternalPath($binaryFileId);
        }

        if ($this->draftIOService->exists($binaryFileId)) {
            return $this->draftIOService->getMimeType($binaryFileId);
        }

        return $this->publishedIOService->getMimeType($binaryFileId);
    }

    public function getFileInputStream(BinaryFile $binaryFile)
    {
        return $this->publishedIOService->getFileInputStream($binaryFile);
    }

    public function deleteBinaryFile(BinaryFile $binaryFile)
    {
        $this->publishedIOService->deleteBinaryFile($binaryFile);
    }

    /**
     * Deletes a directory.
     *
     * @param string $path
     */
    public function deleteDirectory($path)
    {
        $this->publishedIOService->deleteDirectory($path);
    }

    public function newBinaryCreateStructFromUploadedFile(array $uploadedFile)
    {
        return $this->publishedIOService->newBinaryCreateStructFromUploadedFile($uploadedFile);
    }

    /**
     * Checks if $internalPath is a published image path.
     *
     * @param string $internalPath
     *
     * @return bool true if $internalPath is the path to a published image
     */
    protected function isPublishedImagePath($internalPath)
    {
        return strpos($internalPath, $this->publishedPrefix) === 0;
    }

    /**
     * Checks if $internalPath is a published image path.
     *
     * @param string $internalPath
     *
     * @return bool true if $internalPath is the path to a published image
     */
    protected function isDraftImagePath($internalPath)
    {
        return strpos($internalPath, $this->draftPrefix) === 0;
    }
}
