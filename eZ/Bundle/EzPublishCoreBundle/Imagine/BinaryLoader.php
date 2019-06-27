<?php

/**
 * File containing the BinaryLoader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use Exception;
use eZ\Publish\Core\IO\Exception\InvalidBinaryFileIdException;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\IO\Values\MissingBinaryFile;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;

/**
 * Binary loader using eZ IOService.
 * To be used by LiipImagineBundle.
 */
class BinaryLoader implements LoaderInterface
{
    /** @var \eZ\Publish\Core\IO\IOServiceInterface */
    private $ioService;

    /** @var \Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface */
    private $extensionGuesser;

    public function __construct(IOServiceInterface $ioService, ExtensionGuesserInterface $extensionGuesser)
    {
        $this->ioService = $ioService;
        $this->extensionGuesser = $extensionGuesser;
    }

    public function find($path)
    {
        try {
            $binaryFile = $this->ioService->loadBinaryFile($path);
            // Treat a MissingBinaryFile as a not loadable file.
            if ($binaryFile instanceof MissingBinaryFile) {
                throw new NotLoadableException("Source image not found in $path");
            }

            $mimeType = $this->ioService->getMimeType($path);

            return new Binary(
                $this->ioService->getFileContents($binaryFile),
                $mimeType,
                $this->extensionGuesser->guess($mimeType)
            );
        } catch (InvalidBinaryFileIdException $e) {
            $message =
                "Source image not found in $path. Repository images path are expected to be " .
                'relative to the var/<site>/storage/images directory';

            $suggestedPath = preg_replace('#var/[^/]+/storage/images/#', '', $path);
            if ($suggestedPath !== $path) {
                $message .= "\nSuggested value: '$suggestedPath'";
            }

            throw new NotLoadableException($message);
        } catch (Exception $e) {
            throw new NotLoadableException("Source image not found in $path", 0, $e);
        }
    }
}
