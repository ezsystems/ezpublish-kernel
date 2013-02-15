<?php
/**
 * File containing the TextBlock class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\TextBlock;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\ValidationError;

/**
 * The TextBlock field type.
 *
 * Represents a larger body of text, such as text areas.
 */
class Type extends FieldType
{
    protected $settingsSchema = array(
        "textRows" => array(
            "type" => "int",
            "default" => 10
        )
    );

    protected $validatorConfigurationSchema = array();

    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "eztext";
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
        if ( $value === null )
        {
            return "";
        }
        return (string)$value->text;
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\TextBlock\Value
     */
    public function getEmptyValue()
    {
        return new Value;
    }

    /**
     * Returns if the given $value is considered empty by the field type
     *
     * @param mixed $value
     *
     * @return boolean
     */
    public function isEmptyValue( $value )
    {
        return $value === null || $value->text === null || trim( $value->text ) === "";
    }

    /**
     * Implements the core of {@see acceptValue()}.
     *
     * @param mixed $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\TextBlock\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
    {
        if ( $inputValue === "" )
        {
            return $this->getEmptyValue();
        }

        if ( is_string( $inputValue ) )
        {
            $inputValue = new Value( $inputValue );
        }
        else if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\TextBlock\\Value',
                $inputValue
            );
        }

        if ( $inputValue->text === null || $inputValue->text === "" )
        {
            return $this->getEmptyValue();
        }

        if ( !is_string( $inputValue->text ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->text',
                'string',
                $inputValue->text
            );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @return array
     */
    protected function getSortInfo( $value )
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\TextBlock\Value $value
     */
    public function fromHash( $hash )
    {
        if ( $hash === null )
        {
            return null;
        }
        return new Value( $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\TextBlock\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        if ( $this->isEmptyValue( $value ) )
        {
            return null;
        }
        return $value->text;
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return boolean
     */
    public function isSearchable()
    {
        return true;
    }

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

        foreach ( (array)$fieldSettings as $name => $value )
        {
            if ( isset( $this->settingsSchema[$name] ) )
            {
                switch ( $name )
                {
                    case "textRows":
                        if ( !is_integer( $value ) )
                        {
                            $validationErrors[] = new ValidationError(
                                "Setting '%setting%' value must be of integer type",
                                null,
                                array(
                                    "setting" => $name
                                )
                            );
                        }
                        break;
                }
            }
            else
            {
                $validationErrors[] = new ValidationError(
                    "Setting '%setting%' is unknown",
                    null,
                    array(
                        "setting" => $name
                    )
                );
            }
        }

        return $validationErrors;
    }
}
