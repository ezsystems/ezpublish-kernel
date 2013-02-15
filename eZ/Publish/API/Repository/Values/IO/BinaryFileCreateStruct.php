<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\IO\BinaryFileCreateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\IO;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Create struct for BinaryFile objects
 */
class BinaryFileCreateStruct extends ValueObject
{
    /**
     * File mimeType (image/jpeg, audio/mp3, etc) aka contentType
     * @var string
     */
    public $mimeType;

    /**
     * HTTP URI to the binary file
     * @var string
     */
    public $uri;

    /**
     * Original file name
     * @var string
     */
    public $originalFileName;

    /**
     * The size of the file
     * @var int
     */
    public $size;

    /**
     * the input stream
     * @var resource
     */
    public $inputStream;
}
