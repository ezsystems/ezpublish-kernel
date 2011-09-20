<?php
/**
 * File containing the ContentType class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io;

use finfo,
    ezp\Base\Exception\NotFound;

/**
 * This struct describes a file content type, as described in RFC 2045, RFC 2046,
 * RFC 2047, RFC 4288, RFC 4289 and RFC 2049.
 */
class ContentType
{
    public function __construct( $type, $subType )
    {
        $this->type = $type;
        $this->subType = $subType;
    }

    /**
     * Returns the ContentType's string representation: type/subtype
     */
    public function __toString()
    {
        return "$this->type/$this->subType";
    }

    /**
     * Returns a ContentType object from a file path, using fileinfo
     * @param string $path
     * @return ContentType
     * @todo Remove hardcoded dependency on fileinfo, use injection
     */
    public static function getFromPath( $path )
    {
        if ( file_exists( $path ) )
        {
            $finfo = new finfo( FILEINFO_MIME_TYPE );
            $mime = $finfo->file( $path );
            $mimeParts = explode( '/', $mime );
            return new self( $mimeParts[0], $mimeParts[1] );
        }

        throw new NotFound( 'File', $path );
    }

    /**
     * The type (audio, video, text, image)
     * @var string
     */
    public $type;

    /**
     * The subtype (mp3, mp4, plain, jpeg, ...)
     * @var string
     */
    public $subType;
}
