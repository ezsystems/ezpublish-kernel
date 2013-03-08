<?php
/**
 * File containing the Date class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Date;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\ValidationError;
use DateTime;

class Type extends FieldType
{
    /**
     * Default value type empty.
     */
    const DEFAULT_EMPTY = 0;

    /**
     * Default value type current date.
     */
    const DEFAULT_CURRENT_DATE = 1;

    protected $settingsSchema = array(
        // One of the DEFAULT_* class constants
        "defaultType" => array(
            "type" => "choice",
            "default" => self::DEFAULT_EMPTY
        )
    );

    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezdate";
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

        return $value->date->format( "l d F Y" );
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Date\Value
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
     * @return \eZ\Publish\Core\FieldType\Date\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
    {
        if ( is_string( $inputValue ) )
        {
            $inputValue = Value::fromString( $inputValue );
        }
        if ( is_int( $inputValue ) )
        {
            $inputValue = Value::fromTimestamp( $inputValue );
        }
        if ( $inputValue instanceof DateTime )
        {
            $inputValue = new Value( $inputValue );
        }
        else if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                "\$inputValue",
                "eZ\\Publish\\Core\\FieldType\\Date\\Value",
                $inputValue
            );
        }

        if ( isset( $inputValue->value ) && !$inputValue->value instanceof DateTime )
        {
            throw new InvalidArgumentType(
                "$inputValue->value",
                "DateTime",
                $inputValue->value
            );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \eZ\Publish\Core\FieldType\Date\Value $value
     *
     * @return array
     */
    protected function getSortInfo( $value )
    {
        if ( $value === null || $value->date === null )
        {
            return null;
        }

        return $value->date->getTimestamp();
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash Null or associative array containing timestamp and optionally date in RFC850 format.
     *
     * @return \eZ\Publish\Core\FieldType\Date\Value $value
     */
    public function fromHash( $hash )
    {
        if ( $hash === null )
        {
            return null;
        }

        if ( isset( $hash["rfc850"] ) && $hash["rfc850"] )
        {
            return Value::fromString( $hash["rfc850"] );
        }

        return Value::fromTimestamp( (int)$hash["timestamp"] );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\Date\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        if ( $this->isEmptyValue( $value ) )
        {
            return null;
        }

        if ( $value->date instanceof DateTime )
        {
            return array(
                "timestamp" => $value->date->getTimestamp(),
                "rfc850"    => $value->date->format( DateTime::RFC850  ),
            );
        }

        return array(
            "timestamp" => 0,
            "rfc850"    => null,
        );
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

        foreach ( $fieldSettings as $name => $value )
        {
            if ( !isset( $this->settingsSchema[$name] ) )
            {
                $validationErrors[] = new ValidationError(
                    "Setting '%setting%' is unknown",
                    null,
                    array(
                        "setting" => $name
                    )
                );
                continue;
            }

            switch ( $name )
            {
                case "defaultType":
                    $definedTypes = array(
                        self::DEFAULT_EMPTY,
                        self::DEFAULT_CURRENT_DATE
                    );
                    if ( !in_array( $value, $definedTypes ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' is of unknown type",
                            null,
                            array(
                                "setting" => $name
                            )
                        );
                    }
                    break;
            }
        }

        return $validationErrors;
    }
}
