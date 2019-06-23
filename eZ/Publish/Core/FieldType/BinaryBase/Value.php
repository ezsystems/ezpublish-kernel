<?php

/**
 * File containing the BinaryBase Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\BinaryBase;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Base value for binary field types.
 *
 * @property string $path Used for BC with 5.0 (EZP-20948). Equivalent to $id.
 * @property-read string $id Unique file ID, set by storage. Read only since 5.3 (EZP-22808).
 */
abstract class Value extends BaseValue
{
    /**
     * Unique file ID, set by storage.
     *
     * Since 5.3 this is not used for input, use self::$inputUri instead
     *
     * @var null|string
     */
    protected $id;

    /**
     * Input file URI, as a path to a file on a disk.
     *
     * @var string
     */
    public $inputUri;

    /**
     * Display file name.
     *
     * @var string
     */
    public $fileName;

    /**
     * Size of the image file.
     *
     * @var int
     */
    public $fileSize;

    /**
     * Mime type of the file.
     *
     * @var string
     */
    public $mimeType;

    /**
     * HTTP URI.
     *
     * @var string
     */
    public $uri;

    /**
     * Construct a new Value object.
     *
     * @param array $fileData
     */
    public function __construct(array $fileData = [])
    {
        // BC with 5.0 (EZP-20948)
        if (isset($fileData['path'])) {
            $fileData['id'] = $fileData['path'];
            unset($fileData['path']);
        }

        // BC with 5.2 (EZP-22808)
        if (isset($fileData['id']) && file_exists($fileData['id'])) {
            $fileData['inputUri'] = $fileData['id'];
            unset($fileData['id']);
        }

        parent::__construct($fileData);
    }

    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->uri;
    }

    public function __get($propertyName)
    {
        if ($propertyName === 'path') {
            return $this->inputUri;
        }

        return parent::__get($propertyName);
    }

    public function __set($propertyName, $propertyValue)
    {
        // BC with 5.0 (EZP-20948)
        if ($propertyName === 'path') {
            $this->inputUri = $propertyValue;
        } elseif ($propertyName === 'id' && file_exists($propertyValue)) { // BC with 5.2 (EZP-22808)
            $this->inputUri = $propertyValue;
        } else {
            parent::__set($propertyName, $propertyValue);
        }
    }

    public function __isset($propertyName)
    {
        if ($propertyName === 'path') {
            return true;
        }

        return parent::__isset($propertyName);
    }
}
