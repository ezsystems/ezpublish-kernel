<?php

/**
 * File containing the ezimage Type class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Image;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;

/**
 * The Image field type.
 */
class Type extends FieldType
{
    /**
     * @see eZ\Publish\Core\FieldType::$validatorConfigurationSchema
     */
    protected $validatorConfigurationSchema = array(
        'FileSizeValidator' => array(
            'maxFileSize' => array(
                'type' => 'int',
                'default' => null,
            ),
        ),
    );

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezimage';
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Image\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param string|array|\eZ\Publish\Core\FieldType\Image\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Image\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_string($inputValue)) {
            $inputValue = Value::fromString($inputValue);
        }

        if (is_array($inputValue)) {
            if (isset($inputValue['inputUri']) && file_exists($inputValue['inputUri'])) {
                $inputValue['fileSize'] = filesize($inputValue['inputUri']);
                if (!isset($inputValue['fileName'])) {
                    $inputValue['fileName'] = basename($inputValue['inputUri']);
                }
            }

            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\Image\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if (isset($value->inputUri) && !is_string($value->inputUri)) {
            throw new InvalidArgumentType('$value->inputUri', 'string', $value->inputUri);
        }

        if (isset($value->id) && !is_string($value->id)) {
            throw new InvalidArgumentType('$value->id', 'string', $value->id);
        }

        // Required parameter $fileName
        if (!isset($value->fileName) || !is_string($value->fileName)) {
            throw new InvalidArgumentType('$value->fileName', 'string', $value->fileName);
        }

        // Optional parameter $alternativeText
        if (isset($value->alternativeText) && !is_string($value->alternativeText)) {
            throw new InvalidArgumentType(
                '$value->alternativeText',
                'string',
                $value->alternativeText
            );
        }

        if (isset($value->fileSize) && (!is_int($value->fileSize) || $value->fileSize < 0)) {
            throw new InvalidArgumentType(
                '$value->fileSize',
                'int',
                $value->alternativeText
            );
        }
    }

    /**
     * Validates a field based on the validators in the field definition.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\Image\Value $fieldValue The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $fieldValue)
    {
        $errors = array();

        if ($this->isEmptyValue($fieldValue)) {
            return $errors;
        }

        if (isset($fieldValue->inputUri)) {
            $this->validateImageTypeAndContent($fieldValue->inputUri, $errors, 'inputUri');
        }

        // BC: Check if file is a valid image if the value of 'id' matches a local file
        if (isset($fieldValue->id)) {
            $this->validateImageTypeAndContent($fieldValue->id, $errors, 'id');
        }

        foreach ((array)$fieldDefinition->getValidatorConfiguration() as $validatorIdentifier => $parameters) {
            switch ($validatorIdentifier) {
                case 'FileSizeValidator':
                    if (empty($parameters['maxFileSize'])) {
                        // No file size limit
                        break;
                    }

                    // Database stores maxFileSize in MB
                    if (($parameters['maxFileSize'] * 1024 * 1024) < $fieldValue->fileSize) {
                        $errors[] = new ValidationError(
                            'The file size cannot exceed %size% byte.',
                            'The file size cannot exceed %size% bytes.',
                            array(
                                '%size%' => $parameters['maxFileSize'],
                            ),
                            'fileSize'
                        );
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * Validates that the $filePath exists, isn't a PHP file, and has image content.
     *
     * @param string $filePath The file name and path
     * @param ValidationError[] $errors Validation errors, passed by reference
     * @param string $errorContext Context of the error, needed for translation
     */
    private function validateImageTypeAndContent($filePath, &$errors, $errorContext)
    {
        if (
            file_exists($filePath) &&
            (
                strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'php' ||
                !getimagesize($filePath)
            )
        ) {
            $errors[] = new ValidationError('A valid image file is required.', null, array(), $errorContext);
        }
    }

    /**
     * Validates the validatorConfiguration of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * @param mixed $validatorConfiguration
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateValidatorConfiguration($validatorConfiguration)
    {
        $validationErrors = array();

        foreach ($validatorConfiguration as $validatorIdentifier => $parameters) {
            switch ($validatorIdentifier) {
                case 'FileSizeValidator':
                    if (!array_key_exists('maxFileSize', $parameters)) {
                        $validationErrors[] = new ValidationError(
                            'Validator %validator% expects parameter %parameter% to be set.',
                            null,
                            array(
                                '%validator%' => $validatorIdentifier,
                                '%parameter%' => 'maxFileSize',
                            ),
                            "[$validatorIdentifier]"
                        );
                        break;
                    }
                    if (!is_int($parameters['maxFileSize']) && $parameters['maxFileSize'] !== null) {
                        $validationErrors[] = new ValidationError(
                            'Validator %validator% expects parameter %parameter% to be of %type%.',
                            null,
                            array(
                                '%validator%' => $validatorIdentifier,
                                '%parameter%' => 'maxFileSize',
                                '%type%' => 'integer',
                            ),
                            "[$validatorIdentifier][maxFileSize]"
                        );
                    }
                    break;
                default:
                    $validationErrors[] = new ValidationError(
                        "Validator '%validator%' is unknown",
                        null,
                        array(
                            '%validator%' => $validatorIdentifier,
                        ),
                        "[$validatorIdentifier]"
                    );
            }
        }

        return $validationErrors;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSortInfo(BaseValue $value)
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Image\Value $value
     */
    public function fromHash($hash)
    {
        if ($hash === null) {
            return $this->getEmptyValue();
        }

        return new Value($hash);
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\Image\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        return array(
            'id' => $value->id,
            'path' => $value->inputUri ?: $value->id,
            'alternativeText' => $value->alternativeText,
            'fileName' => $value->fileName,
            'fileSize' => $value->fileSize,
            'imageId' => $value->imageId,
            'uri' => $value->uri,
            'inputUri' => $value->inputUri,
            'width' => $value->width,
            'height' => $value->height,
        );
    }

    /**
     * Converts a $value to a persistence value.
     *
     * @param \eZ\Publish\Core\FieldType\Image\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue(SPIValue $value)
    {
        // Store original data as external (to indicate they need to be stored)
        return new FieldValue(
            array(
                'data' => null,
                'externalData' => $this->toHash($value),
                'sortKey' => $this->getSortInfo($value),
            )
        );
    }

    /**
     * Converts a persistence $fieldValue to a Value.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \eZ\Publish\Core\FieldType\Image\Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue)
    {
        if ($fieldValue->data === null) {
            return $this->getEmptyValue();
        }

        // Restored data comes in $data, since it has already been processed
        // there might be more data in the persistence value than needed here
        $result = $this->fromHash(
            array(
                'id' => (isset($fieldValue->data['id'])
                    ? $fieldValue->data['id']
                    : null),
                'alternativeText' => (isset($fieldValue->data['alternativeText'])
                    ? $fieldValue->data['alternativeText']
                    : null),
                'fileName' => (isset($fieldValue->data['fileName'])
                    ? $fieldValue->data['fileName']
                    : null),
                'fileSize' => (isset($fieldValue->data['fileSize'])
                    ? $fieldValue->data['fileSize']
                    : null),
                'uri' => (isset($fieldValue->data['uri'])
                    ? $fieldValue->data['uri']
                    : null),
                'imageId' => (isset($fieldValue->data['imageId'])
                    ? $fieldValue->data['imageId']
                    : null),
                'width' => (isset($fieldValue->data['width'])
                    ? $fieldValue->data['width']
                    : null),
                'height' => (isset($fieldValue->data['height'])
                    ? $fieldValue->data['height']
                    : null),
            )
        );

        return $result;
    }
}
