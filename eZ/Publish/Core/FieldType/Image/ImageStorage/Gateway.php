<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Image\ImageStorage;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\FieldType\StorageGateway;

/**
 * Image Field Type external storage gateway.
 */
abstract class Gateway extends StorageGateway
{
    /**
     * Returns the node path string of $versionInfo.
     *
     * @param VersionInfo $versionInfo
     *
     * @return string
     */
    abstract public function getNodePathString(VersionInfo $versionInfo);

    /**
     * Stores a reference to the image in $path for $fieldId.
     *
     * @param string $uri File IO uri
     * @param mixed $fieldId
     */
    abstract public function storeImageReference($uri, $fieldId);

    /**
     * Returns a the XML content stored for the given $fieldIds.
     *
     * @param int $versionNo
     * @param array $fieldIds
     *
     * @return array
     */
    abstract public function getXmlForImages($versionNo, array $fieldIds);

    /**
     * Removes all references from $fieldId to a path that starts with $path.
     *
     * @param string $uri File IO uri (not legacy uri)
     * @param int $versionNo
     * @param mixed $fieldId
     */
    abstract public function removeImageReferences($uri, $versionNo, $fieldId);

    /**
     * Returns the number of recorded references to the given $path.
     *
     * @param string $uri File IO uri (not legacy uri)
     *
     * @return int
     */
    abstract public function countImageReferences($uri);

    /**
     * Returns true if there is reference to the given $uri.
     */
    abstract public function isImageReferenced(string $uri): bool;

    /**
     * Returns the public uris for the images stored in $xml.
     */
    abstract public function extractFilesFromXml($xml);

    abstract public function getAllVersionsImageXmlForFieldId(int $fieldId): array;

    abstract public function updateImageData(int $fieldId, int $versionNo, string $xml): void;

    abstract public function getImagesData(int $offset, int $limit): array;

    abstract public function updateImagePath(int $fieldId, string $oldPath, string $newPath): void;

    abstract public function countDistinctImagesData(): int;
}
