<?php

/**
 * File containing the BinaryBase Type class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\BinaryBase;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue as PersistenceValue;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Base FileType class for Binary field types (i.e. BinaryBase & Media).
 */
abstract class Type extends FieldType
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
     * Creates a specific value of the derived class from $inputValue.
     *
     * @param array $inputValue
     *
     * @return Value
     */
    abstract protected function createValue(array $inputValue);

    /**
     * @param \eZ\Publish\Core\FieldType\BinaryBase\Value|\eZ\Publish\SPI\FieldType\Value $value
     */
    public function getName(SPIValue $value, FieldDefinition $fieldDefinition, string $languageCode): string
    {
        return (string)$value->fileName;
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param string|array|\eZ\Publish\Core\FieldType\BinaryBase\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\BinaryBase\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput($inputValue)
    {
        // construction only from path
        if (is_string($inputValue)) {
            $inputValue = array('inputUri' => $inputValue);
        }

        // default construction from array
        if (is_array($inputValue)) {
            $inputValue = $this->createValue($inputValue);
        }

        $this->completeValue($inputValue);

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\BinaryBase\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        // Input file URI, if set needs to point to existing file
        if (isset($value->inputUri)) {
            if (!file_exists($value->inputUri)) {
                throw new InvalidArgumentValue(
                    '$value->inputUri',
                    $value->inputUri,
                    get_class($this)
                );
            }
        } elseif (!isset($value->id)) {
            throw new InvalidArgumentValue(
                '$value->id',
                $value->id,
                get_class($this)
            );
        }

        // Required parameter $fileName
        if (!isset($value->fileName) || !is_string($value->fileName)) {
            throw new InvalidArgumentValue(
                '$value->fileName',
                $value->fileName,
                get_class($this)
            );
        }

        // Optional parameter $fileSize
        if (isset($value->fileSize) && !is_int($value->fileSize)) {
            throw new InvalidArgumentValue(
                '$value->fileSize',
                $value->fileSize,
                get_class($this)
            );
        }
    }

    /**
     * Attempts to complete the data in $value.
     *
     * @param \eZ\Publish\Core\FieldType\BinaryBase\Value|\eZ\Publish\Core\FieldType\Value $value
     */
    protected function completeValue(BaseValue $value)
    {
        if (!isset($value->inputUri) || !file_exists($value->inputUri)) {
            return;
        }

        if (!isset($value->fileName)) {
            // @todo this may not always work...
            $value->fileName = basename($value->inputUri);
        }

        if (!isset($value->fileSize)) {
            $value->fileSize = filesize($value->inputUri);
        }
    }

    /**
     * BinaryBase does not support sorting, yet.
     *
     * @param \eZ\Publish\Core\FieldType\BinaryBase\Value $value
     *
     * @return mixed
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
     * @return \eZ\Publish\Core\FieldType\BinaryBase\Value $value
     */
    public function fromHash($hash)
    {
        if ($hash === null) {
            return $this->getEmptyValue();
        }

        return $this->createValue($hash);
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\BinaryBase\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        return array(
            'id' => $value->id,
            // Kept for BC with eZ Publish 5.0 (EZP-20948, EZP-22808)
            'path' => $value->inputUri,
            'inputUri' => $value->inputUri,
            'fileName' => $value->fileName,
            'fileSize' => $value->fileSize,
            'mimeType' => $value->mimeType,
            'uri' => $value->uri,
        );
    }

    /**
     * Converts a $value to a persistence value.
     *
     * In this method the field type puts the data which is stored in the field of content in the repository
     * into the property FieldValue::data. The format of $data is a primitive, an array (map) or an object, which
     * is then canonically converted to e.g. json/xml structures by future storage engines without
     * further conversions. For mapping the $data to the legacy database an appropriate Converter
     * (implementing eZ\Publish\Core\Persistence\Legacy\FieldValue\Converter) has implemented for the field
     * type. Note: $data should only hold data which is actually stored in the field. It must not
     * hold data which is stored externally.
     *
     * The $externalData property in the FieldValue is used for storing data externally by the
     * FieldStorage interface method storeFieldData.
     *
     * The FieldValuer::sortKey is build by the field type for using by sort operations.
     *
     * @see \eZ\Publish\SPI\Persistence\Content\FieldValue
     *
     * @param \eZ\Publish\Core\FieldType\BinaryBase\Value $value The value of the field type
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue the value processed by the storage engine
     */
    public function toPersistenceValue(SPIValue $value)
    {
        // Store original data as external (to indicate they need to be stored)
        return new PersistenceValue(
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
     * This method builds a field type value from the $data and $externalData properties.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \eZ\Publish\Core\FieldType\BinaryBase\Value
     */
    public function fromPersistenceValue(PersistenceValue $fieldValue)
    {
        // Restored data comes in $data, since it has already been processed
        // there might be more data in the persistence value than needed here
        $result = $this->fromHash(
            array(
                'id' => (isset($fieldValue->externalData['id'])
                    ? $fieldValue->externalData['id']
                    : null),
                'fileName' => (isset($fieldValue->externalData['fileName'])
                    ? $fieldValue->externalData['fileName']
                    : null),
                'fileSize' => (isset($fieldValue->externalData['fileSize'])
                    ? $fieldValue->externalData['fileSize']
                    : null),
                'mimeType' => (isset($fieldValue->externalData['mimeType'])
                    ? $fieldValue->externalData['mimeType']
                    : null),
                'uri' => (isset($fieldValue->externalData['uri'])
                    ? $fieldValue->externalData['uri']
                    : null),
            )
        );

        return $result;
    }

    /**
     * Validates a field based on the validators in the field definition.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\BinaryBase\Value $fieldValue The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $fieldValue)
    {
        $errors = array();

        if ($this->isEmptyValue($fieldValue)) {
            return $errors;
        }

        foreach ((array)$fieldDefinition->getValidatorConfiguration() as $validatorIdentifier => $parameters) {
            switch ($validatorIdentifier) {
                // @todo There is a risk if we rely on a user built Value, since the FileSize
                // property can be set manually, making this validation pointless
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
                            "[$validatorIdentifier][maxFileSize]"
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
                                "[$validatorIdentifier][maxFileSize]",
                            )
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
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }
}
