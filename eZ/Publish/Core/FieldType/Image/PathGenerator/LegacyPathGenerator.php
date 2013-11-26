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
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

class LegacyPathGenerator extends PathGenerator
{
    private $publishedImagesDir;

    private $draftImagesDir;

    /**
     * @var string $draftPrefix Prefix for path to draft images
     * @var string $publishedPrefix Prefix for path to published images
     */
    public function __construct( $draftPrefix, $publishedPrefix )
    {
        $this->publishedImagesDir = $publishedPrefix;
        $this->draftImagesDir = $draftPrefix;
    }

    /**
     * Generates the storage path for the field identified by parameters
     *
     * Returns a relative storage path.
     *
     * @param int $status
     * @param mixed $fieldId
     * @param int $versionNo
     * @param string $languageCode
     * @param string $nodePathString
     *
     * @return string
     */
    public function getStoragePathForField( $status, $fieldId, $versionNo, $languageCode, $nodePathString = '' )
    {
        if ( $status === VersionInfo::STATUS_DRAFT )
        {
            return sprintf(
                '%s/%s/%s-%s',
                $this->draftImagesDir,
                $fieldId,
                $versionNo,
                $languageCode
            );
        }
        else
        {
            /**
             * @todo Remove when EZP-21861 is fixed.
             *
             * Should only be required for backports.
             * Ensures that nodePathString doesn't have a leading slash and adds a trailing one if not empty
             */
            if ( $nodePathString != '' )
                $nodePathString = trim( $nodePathString, '/' ) . '/';

            return sprintf(
                '%s/%s%s-%s-%s',
                $this->publishedImagesDir,
                $nodePathString,
                $fieldId,
                $versionNo,
                $languageCode
            );
        }
    }

    public function isPathForDraft( $path )
    {
        return ( strpos( $path, "/" . $this->draftImagesDir . "/" ) !== false );
    }
}
