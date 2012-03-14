<?php
/**
 * File containing the FieldType class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType;
use eZ\Publish\API\Repository\Values\Content\Field,
    eZ\Publish\API\Repository\Repository as BaseRepository,
    eZ\Publish\Core\Repository\FieldType\Validator,
    eZ\Publish\SPI\FieldType\FieldType as FieldTypeInterface,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Base class for field types, the most basic storage unit of data inside eZ Publish.
 *
 * All other field types extend FieldType providing the specific functionality
 * desired in each case.
 *
 * The capabilities supported by each individual field type is decided by which
 * interfaces the field type implements support for. These individual
 * capabilities can also be checked via the supports*() methods.
 *
 * A field type are the base building blocks of Content Types, and serve as
 * data containers for Content objects. Therefore, while field types can be used
 * independently, they are designed to be used as a part of a Content object.
 *
 * Field types are primed and pre-configured with the Field Definitions found in
 * Content Types.
 *
 * @todo Merge and optimize concepts for settings, validator data and field type properties.
 */
abstract class FieldType implements FieldTypeInterface
{
    /**
     * The setting keys which are available on this field type.
     *
     * The key is the setting name, and the value is the default value for given
     * setting, set to null if no particular default should be set.
     *
     * @var array
     */
    protected $allowedSettings = array();

    /**
     * Validators which are supported for this field type.
     * Full Qualified Class Name should be registered here.
     * Example:
     * <code>
     * protected $allowedValidators = array(
     *     "eZ\\Publish\\Core\\Repository\\FieldType\\BinaryFile\\FileSizeValidator"
     * );
     * </code>
     *
     * @var array
     */
    protected $allowedValidators = array();

    /**
     * Constructs field type object, initializing internal data structures.
     */
    public function __construct()
    {
    }

    /**
     * This method is called on occuring events. Implementations can perform corresponding actions
     *
     * @param string $event prePublish, postPublish, preCreate, postCreate
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDef The field definition of the field
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field The field for which an action is performed
     */
    public function handleEvent( $event, BaseRepository $repository, FieldDefinition $fieldDef, Field $field )
    {
    }

    /**
     * Keys of settings which are available on this fieldtype.
     * @return array
     */
    public function allowedSettings()
    {
        return array_keys( $this->allowedSettings );
    }

    /**
     * Return an array of allowed validators to operate on this field type.
     *
     * @return array
     */
    public function allowedValidators()
    {
        return $this->allowedValidators;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     * Return value is an array where key is the sort type, value is field value to be used for sorting.
     * Sort type can be :
     *  - sort_key_string (sorting will be made with a string algorithm)
     *  - sort_key_int (sorting will be made with an integer algorithm)
     *
     * <code>
     * protected function getSortInfo( $value )
     * {
     *     // Example for a text line type:
     *     return array( 'sort_key_string' => $value->text );
     *
     *     // Example for an int:
     *     // return array( 'sort_key_int' => 123 );
     *
     *     // Non sortable:
     *     // return false;
     * }
     * </code>
     *
     * @abstract
     *
     * @param mixed $value
     *
     * @return array|bool Array with sortInfo, or false if the Type doesn't support sorting
     */
    abstract protected function getSortInfo( $value );

    /**
     * Used by the FieldDefinition to populate the $fieldTypeConstraints->validators field.
     *
     * If validator is not allowed for a given field type, no data from that
     * validator is populated to $constraints.
     *
     * @internal
     * @param \eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints $fieldTypeConstraints
     * @param \eZ\Publish\Core\Repository\FieldType\Validator $validator
     * @return void
     */
    public final function fillConstraintsFromValidator( FieldTypeConstraints $fieldTypeConstraints, Validator $validator )
    {
        $validatorClass = get_class( $validator );
        if ( !in_array( $validatorClass, $this->allowedValidators() ) )
            throw new InvalidArgumentException( '$validator', implode( ', ', $this->allowedValidators() ) );

        $fieldTypeConstraints->validators = array(
            $validatorClass => $validator->getValidatorConstraints()
        ) + $fieldTypeConstraints->validators;
    }

    /**
     * Converts a $value to a persistence value
     *
     * @param mixed $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue( $value )
    {
        // @todo Evaluate if creating the sortKey in every case is really needed
        //       Couldn't this be retrieved with a method, which would initialize
        //       that info on request only?
        return new FieldValue(
            array(
                "data" => $this->toHash( $value ),
                "externalData" => null,
                "sortKey" => $this->getSortInfo( $value ),
            )
        );
    }

    /**
     * Converts a persistence $fieldValue to a Value
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return mixed
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        return $this->fromHash( $fieldValue->data );
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return bool
     */
    public function isSearchable()
    {
        return false;
    }
}
