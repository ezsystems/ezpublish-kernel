<?php
/**
 * File containing the DateTime class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\DateAndTime;
use eZ\Publish\Core\FieldType\FieldType,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    DateTime,
    eZ\Publish\Core\FieldType\ValidationError;

class Type extends FieldType
{
    /**
     * Default value types
     */
    const DEFAULT_EMPTY = 0,
          DEFAULT_CURRENT_DATE = 1,
          DEFAULT_CURRENT_DATE_ADJUSTED = 2;

    protected $settingsSchema = array(
        "useSeconds" => array(
            "type" => "bool",
            "default" => false
        ),
        // One of the DEFAULT_* class constants
        "defaultType" => array(
            "type" => "choice",
            "default" => self::DEFAULT_EMPTY
        ),
        /*
         * @var \DateInterval
         * Used only if defaultValueType is set to DEFAULT_CURRENT_DATE_ADJUSTED
         */
        "dateInterval" => array(
            "type" => "dateInterval",
            "default" => null
        )
    );

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezdatetime";
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

        return $value->value->format( 'D Y-d-m H:i:s' );
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\DateAndTime\Value
     */
    public function getEmptyValue()
    {
        return null;
    }

    /**
     * Potentially builds and checks the type and structure of the $inputValue.
     *
     * This method first inspects $inputValue, if it needs to convert it, e.g.
     * into a dedicated value object. An example would be, that the field type
     * uses values of MyCustomFieldTypeValue, but can also accept strings as
     * the input. In that case, $inputValue first needs to be converted into a
     * MyCustomFieldTypeClass instance.
     *
     * After that, the (possibly converted) value is checked for structural
     * validity. Note that this does not include validation after the rules
     * from validators, but only plausibility checks for the general data
     * format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param mixed $inputValue
     *
     * @return mixed The potentially converted and structurally plausible value.
     */
    public function acceptValue( $inputValue )
    {
        if ( $inputValue === null )
        {
            return null;
        }

        if ( is_string( $inputValue ) )
        {
            $inputValue = Value::fromString( $inputValue );
        }
        if ( is_int( $inputValue ) )
        {
            $inputValue = Value::fromTimestamp( $inputValue );
        }
        if ( $inputValue instanceof \DateTime )
        {
            $inputValue = new Value( $inputValue );
        }

        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\DateAndTime\\Value',
                $inputValue
            );
        }

        if ( isset( $inputValue->value ) && !$inputValue->value instanceof DateTime )
        {
            throw new InvalidArgumentType(
                '$inputValue->value',
                'DateTime',
                $inputValue->value
            );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \eZ\Publish\Core\FieldType\DateAndTime\Value $value
     *
     * @return array
     */
    protected function getSortInfo( $value )
    {
        if ( $value === null )
        {
            return null;
        }
        return $value->value->getTimestamp();
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param int $hash Number of seconds since Unix Epoch
     *
     * @return \eZ\Publish\Core\FieldType\DateAndTime\Value $value
     */
    public function fromHash( $hash )
    {
        if ( $hash === null )
        {
            return null;
        }

        if ( isset( $hash['rfc850'] ) && $hash['rfc850'] )
        {
            return Value::fromString( $hash['rfc850'] );
        }

        return Value::fromTimestamp( (int)$hash['timestamp'] );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\DateAndTime\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        if ( $value === null )
        {
            return null;
        }

        if ( $value->value instanceof DateTime )
        {
            return array(
                'timestamp' => $value->value->getTimestamp(),
                'rfc850'    => $value->value->format( \DateTime::RFC850  ),
            );
        }

        return array(
            'timestamp' => 0,
            'rfc850'    => null,
        );
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
            if ( isset( $this->settingsSchema[$name] ) )
            {
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
                            self::DEFAULT_CURRENT_DATE,
                            self::DEFAULT_CURRENT_DATE_ADJUSTED
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
                    case "dateInterval":
                        if ( isset( $value ) )
                        {
                            if ( isset( $fieldSettings["defaultType"] ) &&
                                $fieldSettings["defaultType"] !== self::DEFAULT_CURRENT_DATE_ADJUSTED )
                            {
                                $validationErrors[] = new ValidationError(
                                    "Setting '%setting%' can be used only when setting '%defaultType%' is set to '%DEFAULT_CURRENT_DATE_ADJUSTED%'",
                                    null,
                                    array(
                                        "setting" => $name,
                                        "defaultType" => "defaultType",
                                        "DEFAULT_CURRENT_DATE_ADJUSTED" => "DEFAULT_CURRENT_DATE_ADJUSTED"
                                    )
                                );
                            }
                            elseif ( get_class( $value ) !== "DateInterval" )
                            {
                                $validationErrors[] = new ValidationError(
                                    "Setting '%setting%' value must be an instance of 'DateInterval' class",
                                    null,
                                    array(
                                        "setting" => $name
                                    )
                                );
                            }
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
