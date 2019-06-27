<?php

/**
 * File containing the Media Type class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Media;

use eZ\Publish\Core\FieldType\BinaryBase\Type as BaseType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\fieldType\Value as SPIValue;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class Type extends BaseType
{
    /**
     * List of possible media type settings.
     */
    const TYPE_FLASH = 'flash';
    const TYPE_QUICKTIME = 'quick_time';
    const TYPE_REALPLAYER = 'real_player';
    const TYPE_SILVERLIGHT = 'silverlight';
    const TYPE_WINDOWSMEDIA = 'windows_media_player';
    const TYPE_HTML5_VIDEO = 'html5_video';
    const TYPE_HTML5_AUDIO = 'html5_audio';

    /**
     * Type constants for validation.
     */
    private static $availableTypes = [
        self::TYPE_FLASH,
        self::TYPE_QUICKTIME,
        self::TYPE_REALPLAYER,
        self::TYPE_SILVERLIGHT,
        self::TYPE_WINDOWSMEDIA,
        self::TYPE_HTML5_VIDEO,
        self::TYPE_HTML5_AUDIO,
    ];

    /** @var array */
    protected $settingsSchema = [
        'mediaType' => [
            'type' => 'choice',
            'default' => self::TYPE_HTML5_VIDEO,
        ],
    ];

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezmedia';
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Media\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings($fieldSettings)
    {
        $validationErrors = [];

        foreach ($fieldSettings as $name => $value) {
            if (isset($this->settingsSchema[$name])) {
                switch ($name) {
                    case 'mediaType':
                        if (!in_array($value, self::$availableTypes)) {
                            $validationErrors[] = new ValidationError(
                                "Setting '%setting%' is of unknown type",
                                null,
                                [
                                    '%setting%' => $name,
                                ],
                                "[$name]"
                            );
                        }
                        break;
                }
            } else {
                $validationErrors[] = new ValidationError(
                    "Setting '%setting%' is unknown",
                    null,
                    [
                        '%setting%' => $name,
                    ],
                    "[$name]"
                );
            }
        }

        return $validationErrors;
    }

    /**
     * Creates a specific value of the derived class from $inputValue.
     *
     * @param array $inputValue
     *
     * @return Value
     */
    protected function createValue(array $inputValue)
    {
        return new Value($inputValue);
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\Media\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        parent::checkValueStructure($value);

        if (!is_bool($value->hasController)) {
            throw new InvalidArgumentType(
                '$value->hasController',
                'bool',
                $value->hasController
            );
        }
        if (!is_bool($value->autoplay)) {
            throw new InvalidArgumentType(
                '$value->autoplay',
                'bool',
                $value->autoplay
            );
        }
        if (!is_bool($value->loop)) {
            throw new InvalidArgumentType(
                '$value->loop',
                'bool',
                $value->loop
            );
        }

        if (!is_int($value->height)) {
            throw new InvalidArgumentType(
                '$value->height',
                'int',
                $value->height
            );
        }
        if (!is_int($value->width)) {
            throw new InvalidArgumentType(
                '$value->width',
                'int',
                $value->width
            );
        }
    }

    /**
     * Attempts to complete the data in $value.
     *
     * @param \eZ\Publish\Core\FieldType\Media\Value|\eZ\Publish\Core\FieldType\Value $value
     */
    protected function completeValue(BaseValue $value)
    {
        parent::completeValue($value);

        if (isset($value->hasController) && $value->hasController === null) {
            $value->hasController = false;
        }
        if (isset($value->autoplay) && $value->autoplay === null) {
            $value->autoplay = false;
        }
        if (isset($value->loop) && $value->loop === null) {
            $value->loop = false;
        }

        if (isset($value->height) && $value->height === null) {
            $value->height = 0;
        }
        if (isset($value->width) && $value->width === null) {
            $value->width = 0;
        }
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\Media\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        $hash = parent::toHash($value);

        $hash['hasController'] = $value->hasController;
        $hash['autoplay'] = $value->autoplay;
        $hash['loop'] = $value->loop;
        $hash['width'] = $value->width;
        $hash['height'] = $value->height;

        return $hash;
    }

    /**
     * Converts a persistence $fieldValue to a Value.
     *
     * This method builds a field type value from the $data and $externalData properties.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \eZ\Publish\Core\FieldType\Media\Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue)
    {
        if ($fieldValue->externalData === null) {
            return $this->getEmptyValue();
        }

        $result = parent::fromPersistenceValue($fieldValue);

        $result->hasController = (isset($fieldValue->externalData['hasController'])
            ? $fieldValue->externalData['hasController']
            : false);
        $result->autoplay = (isset($fieldValue->externalData['autoplay'])
            ? $fieldValue->externalData['autoplay']
            : false);
        $result->loop = (isset($fieldValue->externalData['loop'])
            ? $fieldValue->externalData['loop']
            : false);
        $result->height = (isset($fieldValue->externalData['height'])
            ? $fieldValue->externalData['height']
            : 0);
        $result->width = (isset($fieldValue->externalData['width'])
            ? $fieldValue->externalData['width']
            : 0);

        return $result;
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return false;
    }
}
