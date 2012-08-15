<?php
/**
 * File containing the ImageStorage Gateway
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image\ImageStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\Core\FieldType\StorageGateway;

abstract class Gateway extends StorageGateway
{
    /**
     * Returns the node path string of $versionInfo
     *
     * @param VersionInfo $versionInfo
     * @return string
     */
    abstract public function getNodePathString( VersionInfo $versionInfo );

    /**
     * Stores a reference to the image in $path for $fieldId
     *
     * @param string $path
     * @param mixed $fieldId
     * @return void
     */
    abstract public function storeImageReference( $path, $fieldId );

    /**
     * Returns a map of data needed to created a path for $fieldIds
     *
     * @param array $fieldIds
     * @return array
     */
    abstract public function getPathData( array $fieldIds );

    /**
     * Removes all references from $fieldId to a path that starts with $path
     *
     * @param string $path
     * @param mixed $fieldId
     * @return void
     */
    abstract public function removeImageReferences( $path, $fieldId );

    /**
     * Returns the number of recorded references to the given $path
     *
     * @param string $path
     * @return int
     */
    abstract public function countImageReferences( $path );
}

