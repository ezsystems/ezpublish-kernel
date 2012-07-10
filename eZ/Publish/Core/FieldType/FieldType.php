<?php
/**
 * File containing the FieldType class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType;
use eZ\Publish\API\Repository\Values\Content\Field,
    eZ\Publish\API\Repository\FieldTypeTools,
    eZ\Publish\API\Repository\ValidatorService,
    eZ\Publish\Core\FieldType\Validator,
    eZ\Publish\API\Repository\Values\ContentType\Validator as APIValidator,
    eZ\Publish\SPI\FieldType\FieldType as FieldTypeInterface,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\SPI\FieldType\Event;

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
 * Field types are the base building blocks of Content Types, and serve as
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
    protected $settingsSchema = array();

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
     * @var \eZ\Publish\Core\FieldType\Validator[]
     */
    protected $validators = array();

    /**
     * Tool object for field types
     *
     * @var \eZ\Publish\API\Repository\FieldTypeTools
     */
    protected $fieldTypeTools;

    /**
     * Constructs field type object, initializing internal data structures.
     *
     * @param \eZ\Publish\API\Repository\FieldTypeTools $fieldTypeTools
     */
    public function __construct( FieldTypeTools $fieldTypeTools )
    {
        $this->fieldTypeTools = $fieldTypeTools;
    }

    /**
     * This method is called on occurring events. Implementations can perform corresponding actions
     *
     * @param string $event prePublish, postPublish, preCreate, postCreate
     * @param \eZ\Publish\SPI\FieldType\Event $event
     */
    public function handleEvent( Event $event )
    {
    }

    /**
     * Keys of settings which are available on this fieldtype.
     * @return array
     */
    public function getSettingsSchema()
    {
        return $this->settingsSchema;
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
     * Validates a field based on the validators in the field definition
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\Value $fieldValue The field for which an action is performed
     *
     * @return array And array of field validation errors if there were any
     */
    public final function validate( FieldDefinition $fieldDefinition, $fieldValue )
    {
        $errors = array();
        foreach ( (array)$fieldDefinition->getValidators() as $validatorRepresentation )
        {
            $validator = $this->validatorService->getValidator( $validatorRepresentation->identifier );
            $validator->initializeWithConstraints( $validatorRepresentation->constraints );
            if ( !$validator->validate( $fieldValue ) )
            {
                $errors[] = $validator->getMessage();
            }
        }
        return $errors;
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
