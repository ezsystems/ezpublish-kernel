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
     * @param string $nodePathString
     *
     * @return string
     */
    public function getStoragePathForField( $fieldId, $versionNo, $languageCode, $nodePathString )
    {
        return sprintf(
            '%s%s-%s-%s',
            $nodePathString, // note that $nodePathString ends with a "/"
            $fieldId,
            $versionNo,
            $languageCode
        );
    }
}
