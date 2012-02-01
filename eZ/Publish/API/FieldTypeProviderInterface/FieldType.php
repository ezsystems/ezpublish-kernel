<?php
/**
 * @package FieldTypeProviderInterface
 * @author christianbacher
 */
namespace ezp\PublicAPI\FieldTypeProviderInterface;
use ezp\PublicAPI\Interfaces\Repository;
use ezp\PublicAPI\FieldTypeProviderInterface\Value;

/**
 * The field type interface which all field types have to implement.
 *
 * @package FieldTypeProviderInterface
 */
interface FieldType
{
    /**
     *
     * @return the field type identifier for this field type
     */
    public function getFieldTypeIdentifier();

    /**
     *
     * this method is called on occuring events. Implementations can perform corresponding actions
     * @param string $event - prePublish, postPublish, preCreate, postCreate
     * @param Repository $repository
     * @param $fieldDef - the field definition of the field
     * @param $field - the field for which an action is performed
     */
    public function handleEvent( $event, /*Repository*/ $repository, FieldDefinition $fieldDef, Field  $field );

    /**
     * returns a map of allowed setting including a default value used when not given in the field definition
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
     *     "ezp\\Content\\FieldType\\BinaryFile\\FileSizeValidator"
     * );
     * </code>
     *
     * @return array
     */
    public function allowedValidators();

    /**
     *
     * validates a field based on the validators in the field definition
     * @param $fieldDef
     * @param $field
     */
    public function validate( FieldDefinition $fieldDef, Field $field );

    /**
     *
     * Checks the type and structure of the value.
     * @param $value
     * @return Value if the field accepts the given value
     * @throws InvalidArgumentType if the parameter is not of the supported value sub type
     * @throws InvalidArgumentValue if the value does not match the expected structure
     */
    public function acceptValue( $value );

    /**
    * Converts an hash to the Value defined by the field type
    *
     * @param array $hash
     *
     * @return Value
     */
    public function fromHash( array $hash );

    /**
     * Converts a Value to a hash
     *
     * @param Value $value
     *
     * @return array
     */
    public function toHash( Value $value );

    /**
     * converts a Value to a persistence value
     *
     * @param Value $value
     *
     * @return FieldValue
     */
    public function toPersistenceValue( Value $value );

    /**
     * converts a persistence field value to a Value
     *
     * @param FieldValue $fieldValue
     *
     * @return Value
     */
    public function fromPersistenceValue( FieldValue $fieldValue );
}
