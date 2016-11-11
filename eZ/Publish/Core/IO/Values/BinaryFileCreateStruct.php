<?php

/**
 * File containing the eZ\Publish\Core\IO\Values\BinaryFileCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Create struct for BinaryFile objects.
 */
class BinaryFileCreateStruct extends ValueObject
{
    /**
     * URI the binary file should be stored to.
     *
     * @var string
     */
    public $id;

    /**
     * The size of the file.
     *
     * @var int
     */
    public $size;

    /**
     * the input stream.
     *
     * @var resource
     */
    public $inputStream;

    /**
     * The file's mime type
     * If not provided, will be auto-detected by the IOService
     * Example: text/xml.
     *
     * @var string
     */
    public $mimeType;
}
