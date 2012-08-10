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
     * Store the file identified by $sourcePath in a location that corresponds
     * to $targetPath. Returns an identifier of the source file (usually a path).
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @return string
     */
    public function storeFile( $sourcePath, $targetPath );

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

    /**
     * Returns the file size of the file identified by $path
     *
     * @param string $path
     * @return int
     */
    public function getFileSize( $path );
}
