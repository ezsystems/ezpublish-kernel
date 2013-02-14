<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\IO\BinaryFile class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\IO;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class provides an abstract access to binary files.
 *
 * It allows reading & writing of files in a unified way
 *
 * @property-read string $id The id of the binary file
 * @property-read int $size File size, in bytes
 * @property-read int $mtime File modification time
 * @property-read int $ctime File creation time
 * @property-read string $mimeType File mimeType (image/jpeg, audio/mp3, etc)
 * @property-read string $uri HTTP URI to the binary file
 * @property-read string $originalFile Original file name
 */

class BinaryFile extends ValueObject
{
    /**
     * The id of the binary file
     * @var string
     */
    protected $id;

    /**
     * File size, in bytes
     * @var int
     */
    protected $size;

    /**
     * File modification time
     * @var \DateTime
     */
    protected $mtime;

    /**
     * File creation time
     * @var \DateTime
     */
    protected $ctime;

    /**
     * File mimeType (image/jpeg, audio/mp3, etc) aka contentType
     * @var string
     */
    protected $mimeType;

    /**
     * HTTP URI to the binary file
     * @var string
     */
    protected $uri;

    /**
     * Original file name
     * @var string
     */
    protected $originalFile;
}
