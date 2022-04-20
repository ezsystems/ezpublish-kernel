<?php

/**
 * File containing the ImageStorage Gateway.
 *
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
     * Returns the number of recorded references outside of the given $path.
     *
     * @param string $uri File IO uri (not legacy uri)
     *
     * @return int
     */
    abstract public function countImageReferencesOutsidePath($uri);

    /**
     * Returns references outside of the given $path.
     *
     * @param string $uri File IO uri (not legacy)
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    abstract public function getImagesOutsidePath($uri, $limit = null, $offset = 0);

    /**
     * Updates the filepath of given Image.
     *
     * @param $imageId
     * @param $newFilePath
     */
    abstract public function updateImageFilePath($imageId, $newFilePath);

    /**
     * Returns the public uris for the images stored in $xml.
     */
    abstract public function extractFilesFromXml($xml);
}
