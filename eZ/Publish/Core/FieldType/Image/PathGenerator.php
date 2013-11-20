<?php
/**
 * File containing the PathGenerator base class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image;

abstract class PathGenerator
{
    /**
     * Generates the path for the field identified by parameters
     *
     * Returns a relative storage path.
     *
     * @param int $status a status from VersionInfo
     * @param mixed $fieldId
     * @param int $versionNo
     * @param string $languageCode
     * @param string $nodePathString
     *
     * @return string
     */
    abstract public function getStoragePathForField( $status, $fieldId, $versionNo, $languageCode, $nodePathString = '' );

    /**
     * Tells if $path is the path to an image draft
     *
     * @param $path
     *
     * @return bool
     */
    abstract public function isPathForDraft( $path );
}
