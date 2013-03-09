<?php
/**
 * File containing the \eZ\Publish\SPI\IO\BinaryFile class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\IO;

/**
 * This class provides an abstract access to binary files.
 *
 * It allows reading & writing of files in a unified way
 */

class BinaryFile
{
    /**
     * File size, in bytes
     *
     * @var int
     */
    public $size;

    /**
     * File modification time
     *
     * @var \DateTime
     */
    public $mtime;

    /**
     * HTTP URI to the binary file
     *
     * @var string
     */
    public $uri;

    /**
     * The file's mime type
     * If not provided, will be auto-detected by the IOService
     * Example: text/xml
     * @var string
     */
    public $mimeType;
}
