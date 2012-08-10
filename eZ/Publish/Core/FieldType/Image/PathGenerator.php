<?php
/**
 * File containing the PathGenerator base class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image;
use eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field;

abstract class PathGenerator
{
    /**
     * Generates the storage path for the given $field
     *
     * Returns a relative storage path for the given $field.
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     * @return string
     */
    abstract public function getStoragePathForField( VersionInfo $versionInfo, Field $field, $nodePathString );
}
