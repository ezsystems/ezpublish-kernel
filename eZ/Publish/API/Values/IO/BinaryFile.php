<?php
namespace eZ\Publish\API\Values\IO;

/**
 * This class provides an abstract access to binary files.
 *
 * It allows reading & writing of files in a unified way
 * 
 * @property-read string $id The id of the binary file
 * @property-read int $size File size, in bytes
 * @property-read int $mtime File modification time
 * @property-read int $ctime File creation time
 * @property-read string $contentType File contentType (image/jpeg, audio/mp3, etc)
 * @property-read string $uri HTTP URI to the binary file
 * @property-read string $originalFile Original file name
 */

class BinaryFile
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
     * File contentType (image/jpeg, audio/mp3, etc)
     * @var string
     */
    protected $contentType;

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
