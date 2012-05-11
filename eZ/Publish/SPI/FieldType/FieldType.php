<?php
/**
 * File containing the FieldType interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\FieldType;

use eZ\Publish\API\Repository\Values\Content\Field,
    eZ\Publish\API\Repository\FieldTypeService,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\SPI\Persistence\Content\FieldValue;

/**
 * The field type interface which all field types have to implement.
 *
 * @package FieldTypeProviderInterface
 */
interface FieldType
{
    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier();

    /**
     * This method is called on occuring events. Implementations can perform corresponding actions
     *
     * @param string $event prePublish, postPublish, preCreate, postCreate
     * @param \eZ\Publish\API\Repository\FieldTypeService $fieldTypeService
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDef The field definition of the field
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field The field for which an action is performed
     */
    public function handleEvent( $event, FieldTypeService $fieldTypeService, FieldDefinition $fieldDef, Field $field );

    /**
     * Returns a map of allowed setting including a default value used when not given in the field definition
     *
     * @return array
     */
    public function allowedSettings();

    /**
     * The method returns the validators which are supported for this field type.
     * Full Qualified Class Name should be registered here.
     * Example:
     * <code>
     * array(
     *     "eZ\\Publish\\Core\\Repository\\FieldType\\BinaryFile\\FileSizeValidator"
     * );
     * </code>
     *
     * @return array
     */
    public function allowedValidators();

    /**
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $plainValue as value.
     *
     * @param mixed $plainValue
     * @return \eZ\Publish\Core\Repository\FieldType\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $plainValue );

    /**
     * Validates a field based on the validators in the field definition
     *
     * @todo Implementing this in all FieldTypes
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDef The field definition of the field
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field The field for which an action is performed
     */
    //public function validate( FieldDefinition $fieldDef, Field $field );

    /**
     * Indicates if the field type supports indexing and sort keys for searching
     *
     * @return bool
     */
    public function isSearchable();

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return mixed
     */
    public function getDefaultDefaultValue();

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param mixed $inputValue
     *
     * @return mixed
     */
    public function acceptValue( $inputValue );

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return mixed
     */
    public function fromHash( $hash );

    /**
     * Converts a Value to a hash
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function toHash( $value );

    /**
     * Converts a $value to a persistence value
     *
     * @param mixed $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue( $value );

    /**
     * Converts a persistence $fieldValue to a Value
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return mixed
     */
    public function fromPersistenceValue( FieldValue $fieldValue );
}
