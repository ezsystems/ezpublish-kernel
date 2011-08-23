<?php
/**
 * File containing the ContentType class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io;

/**
 * This struct describes a file content type, as described in RFC 2045, RFC 2046,
 * RFC 2047, RFC 4288, RFC 4289 and RFC 2049.
 */
class ContentType
{
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

?>