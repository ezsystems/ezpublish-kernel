<?php
/**
 * File containing the DateTime class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\DateAndTime;
use eZ\Publish\Core\Repository\FieldType,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Base\Exception\InvalidArgumentType,
    DateTime;

class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = "ezdatetime";
    const IS_SEARCHABLE = true;

    /**
     * Default value types
     */
    const DEFAULT_EMPTY = 0,
          DEFAULT_CURRENT_DATE = 1,
          DEFAULT_CURRENT_DATE_ADJUSTED = 2;

    protected $allowedSettings = array(
        'useSeconds' => false,
        // One of the DEFAULT_* class constants
        'defaultType' => self::DEFAULT_EMPTY,
        /*
         * @var \DateInterval
         * Used only if defaultValueType is set to DEFAULT_CURRENT_DATE_ADJUSTED
         */
        'dateInterval' => null
    );

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value
     */
    public function getDefaultValue()
    {
        return new Value;
    }

    /**
     * Checks if value can be parsed.
     *
     * If the value actually can be parsed, the value is returned.
     *
     * @throws ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param mixed $inputValue
     * @return mixed
     */
    protected function canParseValue( BaseValue $inputValue )
    {
        if ( $inputValue instanceof Value )
        {
            if ( isset( $inputValue->value ) && !$inputValue->value instanceof DateTime )
                throw new BadFieldTypeInput( $inputValue, get_class( $this ) );

            return $inputValue;
        }

        throw new InvalidArgumentType( 'value', 'eZ\\Publish\\Core\\Repository\\FieldType\\DateAndTime\\Value' );
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @return array
     */
    protected function getSortInfo( BaseValue $value )
    {
        $timestamp = 0;
        if ( $value->value instanceof DateTime )
            $timestamp = $value->value->getTimestamp();

        return array( 'sort_key_int' => $timestamp );
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param int $hash Number of seconds since Unix Epoch
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( "@$hash" );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\Repository\FieldType\Value $value
     *
     * @return mixed
     */
    public function toHash( BaseValue $value )
    {
        return $value->value->getTimestamp();
    }
}
