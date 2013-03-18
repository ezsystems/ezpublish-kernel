<?php
/**
 * File containing the eZ\Publish\Core\IO\Values\BinaryFile class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class provides an abstract access to binary files.
 *
 * It allows reading & writing of files in a unified way
 *
 * @property-read string $id The id of the binary file
 * @property-read int $mtime File modification time
 * @property-read string $uri HTTP URI to the binary file
 * @property-read int $size File size
 */
class BinaryFile extends ValueObject
{
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
     * URI to the binary file
     * @var string
     */
    protected $uri;

    /**
     * The file's mime type
     * Example: text/xml
     * @var string
     */
    public $mimeType;

}
