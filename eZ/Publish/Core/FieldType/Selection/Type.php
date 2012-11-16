<?php
/**
 * File containing the Selection class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Selection;
use eZ\Publish\Core\FieldType\FieldType,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    eZ\Publish\Core\FieldType\ValidationError;

/**
 * The Selection field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    /**
     * The setting keys which are available on this field type.
     *
     * The key is the setting name, and the value is the default value for given
     * setting, set to null if no particular default should be set.
     *
     * @var mixed
     */
    protected $settingsSchema = array(
        'isMultiple' => array(
            'type' => 'bool',
            'default' => false,
        ),
        'options' => array(
            'type' => 'hash',
            'default' => array(),
        ),
    );

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings( $fieldSettings )
    {
        $validationErrors = array();

        if ( !is_array( $fieldSettings ) )
        {
            $validationErrors[] = new ValidationError(
                "FieldType '%fieldType%' expects settings to be a hash map",
                null,
                array(
                    "fieldType" => $this->getFieldTypeIdentifier()
                )
            );
            return $validationErrors;
        }

        foreach ( $fieldSettings as $settingKey => $settingValue )
        {
            switch ( $settingKey )
            {
                case 'isMultiple':
                    if ( !is_bool( $settingValue ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "FieldType '%fieldType%' expects setting %setting% to be a of type %type%",
                            null,
                            array(
                                "fieldType" => $this->getFieldTypeIdentifier(),
                                "setting"   => $settingKey,
                                "type"      => "bool",
                            )
                        );
                    }
                    break;
                case 'options':
                    if ( !is_array( $settingValue ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "FieldType '%fieldType%' expects setting %setting% to be a of type %type%",
                            null,
                            array(
                                "fieldType" => $this->getFieldTypeIdentifier(),
                                "setting"   => $settingKey,
                                "type"      => "hash",
                            )
                        );
                    }
                    break;
                default:
                    $validationErrors[] = new ValidationError(
                        "Setting '%setting%' is unknown",
                        null,
                        array(
                            "setting" => $settingKey
                        )
                    );
            }
        }

        return $validationErrors;
    }

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezselection";
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getName( $value )
    {
        throw new \RuntimeException( 'Implement this method' );
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Selection\Value
     */
    public function getEmptyValue()
    {
        return new Value;
    }

    /**
     * Implements the core of {@see acceptValue()}.
     *
     * @param mixed $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Selection\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
    {
        if ( is_array( $inputValue ) )
        {
            $inputValue = new Value( $inputValue );
        }
        else if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\Selection\\Value',
                $inputValue
            );
        }

        if ( !is_array( $inputValue->selection ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->selection',
                'array',
                $inputValue->selection
            );
        }

        return $inputValue;
    }

    /**
     * Validates field value against 'isMultiple' and 'options' settings.
     *
     * Does not use validators.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\Value $fieldValue The field for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate( FieldDefinition $fieldDefinition, $fieldValue )
    {
        $validationErrors = array();
        $fieldSettings = $fieldDefinition->fieldSettings;

        if ( ( !isset( $fieldSettings["isMultiple"] ) || $fieldSettings["isMultiple"] === false )
            && count( $fieldValue->selection ) > 1 )
        {
            $validationErrors[] = new ValidationError(
                "Field definition does not allow multiple options to be selected.",
                null,
                array()
            );
        }

        foreach ( $fieldValue->selection as $optionIndex )
        {
            if ( !isset( $fieldSettings["options"][$optionIndex] ) )
            {
                $validationErrors[] = new ValidationError(
                    "Option with index %index% does not exist in the field definition.",
                    null,
                    array(
                        "index" => $optionIndex
                    )
                );
            }
        }

        return $validationErrors;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @return array
     */
    protected function getSortInfo( $value )
    {
        return implode( '-', $value->selection );
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Selection\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\Selection\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return $value->selection;
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }

    /**
     * Get index data for field data for search backend
     *
     * @param mixed $value
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Field[]
     */
    public function getIndexData( $value )
    {
        throw new \RuntimeException( '@TODO: Implement' );
    }
}
