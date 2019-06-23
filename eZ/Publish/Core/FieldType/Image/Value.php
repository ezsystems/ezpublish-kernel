<?php

/**
 * File containing the Image Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Image;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;

/**
 * Value for Image field type.
 *
 * @property string $path @deprecated BC with 5.0 (EZP-20948). Equivalent to $id or $inputUri, depending on which one is set
 * .
 *
 * @todo Mime type?
 * @todo Dimensions?
 */
class Value extends BaseValue
{
    /**
     * Image id.
     *
     * Required.
     *
     * @var mixed
     */
    public $id;

    /**
     * The alternative image text (for example "Picture of an apple.").
     *
     * @var string|null
     */
    public $alternativeText;

    /**
     * Display file name of the image.
     *
     * Required.
     *
     * @var string
     */
    public $fileName;

    /**
     * Size of the image file.
     *
     * Required.
     *
     * @var int
     */
    public $fileSize;

    /**
     * The image's HTTP URI.
     *
     * @var string
     */
    public $uri;

    /**
     * External image ID (required by REST for now, see https://jira.ez.no/browse/EZP-20831).
     *
     * @var mixed
     */
    public $imageId;

    /**
     * Input image file URI.
     *
     * @var string
     */
    public $inputUri;

    /**
     * Original image width.
     *
     * @var int
     */
    public $width;

    /**
     * Original image height.
     *
     * @var int
     */
    public $height;

    /**
     * Construct a new Value object.
     *
     * @param array $imageData
     */
    public function __construct(array $imageData = [])
    {
        foreach ($imageData as $key => $value) {
            try {
                $this->$key = $value;
            } catch (PropertyNotFoundException $e) {
                throw new InvalidArgumentType(
                    sprintf('Image\Value::$%s', $key),
                    'Existing property',
                    $value
                );
            }
        }
    }

    /**
     * Creates a value only from a file path.
     *
     * @param string $path
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     *
     * @return Value
     *
     * @deprecated Starting with 5.3.3, handled by Image\Type::acceptValue()
     */
    public static function fromString($path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentType(
                '$path',
                'existing file',
                $path
            );
        }

        return new static(
            [
                'inputUri' => $path,
                'fileName' => basename($path),
                'fileSize' => filesize($path),
            ]
        );
    }

    /**
     * Returns the image file size in byte.
     *
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->fileName;
    }

    public function __get($propertyName)
    {
        if ($propertyName === 'path') {
            return $this->inputUri ?: $this->id;
        }

        throw new PropertyNotFoundException($propertyName, get_class($this));
    }

    public function __set($propertyName, $propertyValue)
    {
        if ($propertyName === 'path') {
            $this->inputUri = $propertyValue;

            return;
        }

        throw new PropertyNotFoundException($propertyName, get_class($this));
    }
}
