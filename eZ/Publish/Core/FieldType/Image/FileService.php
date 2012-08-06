<?php
/**
 * File containing the FileService interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image;
use eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field;

interface FileService
{
    /**
     * Store the file identified by $inputPath returning an identifying path
     * for the storage location
     *
     * @param string $inputPath
     * @param VersionInfo $versionInfo
     * @param Field $field
     * @return string
     * @todo Avoid $connection parameter?
     */
    public function storeFile( VersionInfo $versionInfo, Field $field, $connection );

    /**
     * Returns a hash of meta data
     *
     * array(
     *  'width' => <int>,
     *  'height' => <int>,
     *  'mime' => <string>,
     * );
     *
     * @param string $path
     * @return array
     */
    public function getMetaData( $path );
}
