<?php
/**
 * File containing the \ezp\Persistence\BinaryFile class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Io;

/**
 * This class provides an abstract access to binary files.
 *
 * It allows reading & writing of files in a unified way
 */

class BinaryFile
{
    /**
     * Relative path to the file
     * @var string
     */
    public $path;

    /**
     * File size, in bytes
     * @var int
     */
    public $size;

    /**
     * File modification time
     * @var \DateTime
     */
    public $mtime;

    /**
     * File creation time
     * @var \DateTime
     */
    public $ctime;

    /**
     * File contentType (image/jpeg, audio/mp3, etc)
     * @var \eZ\Publish\SPI\Io\ContentType
     */
    public $contentType;

    /**
     * HTTP URI to the binary file
     * @var string
     */
    public $uri;

    /**
     * Original file name
     * @var string
     */
    public $originalFile;
}
