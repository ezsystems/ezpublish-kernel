<?php

namespace eZ\Publish\API\Values\IO;

/**
 * Create struct for BinaryFile objects
 */
class BinaryFileCreateStruct
{
     /**
     * File contentType (image/jpeg, audio/mp3, etc)
     * @var \eZ\Publish\API\Values\IO\ContentType
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
    public $originalFileName;

    /**
     * The size of the file
     * @var int
     */
    public $size;

    /**
     *
     * the input stream
     * @var resource
     */
    public $inputStream;
}
