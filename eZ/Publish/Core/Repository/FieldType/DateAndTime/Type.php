<?php
/**
 * File containing the DateTime class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\DateAndTime;
use eZ\Publish\Core\Repository\FieldType\FieldType,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    DateTime;

class Type extends FieldType
{
    /**
     * Default value types
     */
    const DEFAULT_EMPTY = 0,
          DEFAULT_CURRENT_DATE = 1,
          DEFAULT_CURRENT_DATE_ADJUSTED = 2;

    protected $settingSchema = array(
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
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $dateTime as value.
     *
     * @param \DateTime|string $dateTime
     * @return \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $dateTime )
    {
        return new Value( $dateTime );
    }

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
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value
     */
    public function getDefaultDefaultValue()
    {
        return new Value();
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value $inputValue
     *
     * @return \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value
     */
    public function acceptValue( $inputValue )
    {
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\Repository\\FieldType\\DateAndTime\\Value',
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
     * @return array
     */
    protected function getSortInfo( $value )
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
     * @return \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( "@$hash" );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return $value->value->getTimestamp();
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
}
