<?php
/**
 * File containing the Time class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Time;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\ValidationError;
use DateTime;

class Type extends FieldType
{
    /**
     * Default value type empty
     */
    const DEFAULT_EMPTY = 0;

    /**
     * Default value type current date
     */
    const DEFAULT_CURRENT_TIME = 1;

    protected $settingsSchema = array(
        "useSeconds" => array(
            "type" => "bool",
            "default" => false
        ),
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
        return "eztime";
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
            return '';
        }

        $dateTime = new DateTime( "@{$value->time}" );
        return $dateTime->format( 'g:i:s a' );
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Time\Value
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
     * @return \eZ\Publish\Core\FieldType\Time\Value The potentially converted and structurally plausible value.
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
            $inputValue = Value::fromDateTime( $inputValue );
        }
        else if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\Time\\Value',
                $inputValue
            );
        }

        if ( isset( $inputValue->time ) && !is_int( $inputValue->time ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->time',
                'DateTime',
                $inputValue->time
            );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \eZ\Publish\Core\FieldType\Time\Value $value
     *
     * @return array
     */
    protected function getSortInfo( $value )
    {
        if ( $value === null || $value->time === null )
        {
            return null;
        }

        return $value->time;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param int $hash Number of seconds since Unix Epoch
     *
     * @return \eZ\Publish\Core\FieldType\Time\Value $value
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
     * @param \eZ\Publish\Core\FieldType\Time\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        if ( $this->isEmptyValue( $value ) )
        {
            return null;
        }

        return $value->time;
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
            }

            switch ( $name )
            {
                case "useSeconds":
                    if ( !is_bool( $value ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of boolean type",
                            null,
                            array(
                                "setting" => $name
                            )
                        );
                    }
                    break;
                case "defaultType":
                    $definedTypes = array(
                        self::DEFAULT_EMPTY,
                        self::DEFAULT_CURRENT_TIME
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
