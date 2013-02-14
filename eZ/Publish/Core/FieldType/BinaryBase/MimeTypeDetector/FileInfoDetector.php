<?php
/**
 * File containing the MimeTypeDetector interface
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\BinaryBase\MimeTypeDetector;

use eZ\Publish\Core\FieldType\BinaryBase\MimeTypeDetector;

class FileInfoDetector implements MimeTypeDetector
{
    /**
     * Magic FileInfo object
     *
     * @var \finfo
     */
    protected $fileInfo;

    /**
     * Checks for the required ext/fileinfo
     */
    public function __construct()
    {
        // Enabled by default since 5.3. Still checking if someone disabled
        // manually.
        if ( !extension_loaded( 'fileinfo' ) )
        {
            throw new \RuntimeException( 'The extension "ext/fileinfo" must be loaded in order for this class to work.' );
        }
    }

    /**
     * Returns the MIME type of the file identified by $path
     *
     * @param string $path
     *
     * @return string
     */
    public function getMimeType( $path )
    {
        return $this->getFileInfo()->file( $path );
    }

    /**
     * Creates a new (or re-uses) finfo object and returns it
     *
     * @return \finfo
     */
    protected function getFileInfo()
    {
        if ( !isset( $this->fileInfo ) )
        {
            $this->fileInfo = new \finfo( FILEINFO_MIME_TYPE );
        }
        return $this->fileInfo;
    }
}
