<?php
/**
 * File containing the MimeTypeDetector interface
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\BinaryBase;

interface MimeTypeDetector
{
    /**
     * Returns the MIME type of the file identified by $path
     *
     * @param string $path
     *
     * @return string
     */
    public function getMimeType( $path );
}
