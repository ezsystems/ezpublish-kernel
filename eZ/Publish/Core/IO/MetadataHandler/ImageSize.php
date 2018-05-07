<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\MetadataHandler;

use eZ\Publish\Core\IO\MetadataHandler;
use Imagine\Exception\RuntimeException;
use Imagine\Image\ImagineInterface;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;

class ImageSize implements MetadataHandler
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var ImagineInterface
     */
    private $imagine;

    public function __construct(FilesystemInterface $filesystem, ImagineInterface $imagine)
    {
        $this->filesystem = $filesystem;
        $this->imagine = $imagine;
    }

    /**
     * Extract file metadata.
     *
     * @param string $filePath
     * @return array
     */
    public function extract($filePath)
    {
        try {
            $imageAsString = $this->filesystem->read($filePath);
            $imageSize = $this->imagine->load($imageAsString)->getSize();
        } catch (FileNotFoundException $e) {
            return [];
        } catch (RuntimeException $e) {
            return [];
        }

        return [
            'width' => $imageSize->getWidth(),
            'height' => $imageSize->getHeight(),
        ];
    }
}
