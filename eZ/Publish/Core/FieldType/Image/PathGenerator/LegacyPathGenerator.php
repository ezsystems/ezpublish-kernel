<?php
/**
 * File containing the PathGenerator implementation compatible to the legacy
 * kernel.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image\PathGenerator;
use eZ\Publish\Core\FieldType\Image\PathGenerator,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field;

class LegacyPathGenerator extends PathGenerator
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
    public function getStoragePathForField( VersionInfo $versionInfo, Field $field, $nodePathString )
    {
        return sprintf(
            '%s%s-%s-%s',
            $nodePathString, // note that $nodePathString ends with a "/"
            $field->id,
            $versionInfo->versionNo,
            $field->languageCode
        );
    }
}
