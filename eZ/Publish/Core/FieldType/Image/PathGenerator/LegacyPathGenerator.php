<?php
/**
 * File containing the PathGenerator implementation compatible to the legacy
 * kernel.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image\PathGenerator;

use eZ\Publish\Core\FieldType\Image\PathGenerator;

class LegacyPathGenerator extends PathGenerator
{
    /**
     * Generates the storage path for the field identified by parameters
     *
     * Returns a relative storage path.
     *
     * @param mixed $fieldId
     * @param int $versionNo
     * @param string $languageCode
     *
     * @return string
     */
    public function getStoragePathForField( $fieldId, $versionNo, $languageCode )
    {
        return sprintf(
            '%s/%s-%s-%s',
            $this->getDirectoryStructure( $fieldId ),
            $fieldId,
            $versionNo,
            $languageCode
        );
    }

    /**
     * Computes a 4 levels directory structure from $id
     * @param string $id
     * @return string
     */
    private function getDirectoryStructure( $id )
    {
        // our base string is the last 4 digits, defaulting to 0, reversed
        $idString = strrev(
            substr( str_pad( $id, 4, 0, STR_PAD_LEFT ), -4 )
        );

        return trim(
            chunk_split( $idString, 1, "/" ),
            "/"
        );
    }
}
