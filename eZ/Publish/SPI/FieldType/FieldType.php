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
    eZ\Publish\API\Repository\ValidatorService,
    eZ\Publish\Core\Repository\FieldType\Validator,
    eZ\Publish\API\Repository\Values\ContentType\Validator as APIValidator,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

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
     * @TODO Expose to Public API.
     */
    public function getFieldTypeIdentifier();

    /**
     * This method is called on occurring events. Implementations can perform corresponding actions
     *
     * @param string $event prePublish, postPublish, preCreate, postCreate
     * @param \eZ\Publish\API\Repository\FieldTypeService $fieldTypeService
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDef The field definition of the field
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field The field for which an action is performed
     * @TODO Add VersionInfo parameter
     */
    public function handleEvent( $event, FieldTypeService $fieldTypeService, FieldDefinition $fieldDef, Field $field );

    /**
     * Returns a schema for the settings expected by the FieldType
     *
     * Returns an arbitrary value, representing a schema for the settings of
     * the FieldType.
     *
     * Explanation: There are no possible generic schemas for defining settings
     * input, which is why no schema for the return value of this method is
     * defined. It is up to the implementor to define and document a schema for
     * the return value and document it. In addition, it is necessary that all
     * consumers of this interface (e.g. Public API, REST API, GUIs, ...)
     * provide plugin mechanisms to hook adapters for the specific FieldType
     * into. These adapters then need to be either shipped with the FieldType
     * or need to be implemented by a third party. If there is no adapter
     * available for a specific FieldType, it will not be usable with the
     * consumer.
     *
     * @return mixed
     * @TODO Expose to Public API.
     */
    public function getSettingsSchema();

    /**
     * Returns a schema for the validator configuration expected by the FieldType
     *
     * Returns an arbitrary value, representing a schema for the validator
     * configuration of the FieldType.
     *
     * Explanation: There are no possible generic schemas for defining settings
     * input, which is why no schema for the return value of this method is
     * defined. It is up to the implementor to define and document a schema for
     * the return value and document it. In addition, it is necessary that all
     * consumers of this interface (e.g. Public API, REST API, GUIs, ...)
     * provide plugin mechanisms to hook adapters for the specific FieldType
     * into. These adapters then need to be either shipped with the FieldType
     * or need to be implemented by a third party. If there is no adapter
     * available for a specific FieldType, it will not be usable with the
     * consumer.
     *
     * Best practice:
     *
     * It is considered best practice to return a hash map, which contains
     * rudimentary settings structures, like e.g. for the "ezstring" FieldType
     *
     * <code>
     *  array(
     *      'stringLength' => array(
     *          'minStringLength' => array(
     *              'type'    => 'int',
     *              'default' => 0,
     *          ),
     *          'maxStringLength' => array(
     *              'type'    => 'int'
     *              'default' => null,
     *          )
     *      ),
     *  );
     * </code>
     *
     * @return mixed
     * @TODO Expose to Public API.
     */
    public function getValidatorConfigurationSchema();

    /**
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $plainValue as value.
     *
     * @param mixed $plainValue
     * @return \eZ\Publish\API\Repository\FieldType\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $plainValue );

    /**
     * Validates a field based on the validators in the field definition
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDef The field definition of the field
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field The field for which an action is performed
     *
     * @return array An array of field validation errors if there were any
     */
    public function validate( FieldDefinition $fieldDef, $field );

    /**
     * Indicates if the field type supports indexing and sort keys for searching
     *
     * @return bool
     * @TODO Expose to Public API.
     */
    public function isSearchable();

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return mixed
     * @TODO Expose to Public API.
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
     * @TODO Expose to Public API.
     * @TODO May support different formats, but best practice is only 1
     */
    public function fromHash( $hash );

    /**
     * Converts a Value to a hash
     *
     * @param mixed $value
     *
     * @return mixed
     * @TODO Expose to Public API.
     * @TODO May support different formats, but best practice is only 1
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
