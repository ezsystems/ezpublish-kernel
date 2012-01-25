<?php
namespace ezp\PublicAPI\Values\IO;

/**
 * This class provides an abstract access to binary files.
 *
 * It allows reading & writing of files in a unified way
 */

class BinaryFile
{
    /**
     * The id of the binary file
     * @var string
     */
    public $id;

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
     * @var \ezp\PublicAPI\Values\IO\ContentType
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
