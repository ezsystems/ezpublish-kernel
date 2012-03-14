<?php
/**
 * File containing the Rating field type
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Rating;
use eZ\Publish\Core\Repository\FieldType,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * Rating field types
 *
 * Represents rating.
 */
class Type extends FieldType
{
    protected $allowedSettings = array();

    /**
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $isDisabled as value.
     *
     * @param bool $isDisabled
     * @return \eZ\Publish\Core\Repository\FieldType\Rating\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $isDisabled )
    {
        return new Value( $isDisabled );
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Rating\Value
     */
    public function getDefaultDefaultValue()
    {
        return new Value();
    }

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezsrrating";
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\Repository\FieldType\Rating\Value $inputValue
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Rating\Value
     */
    public function acceptValue( $inputValue )
    {
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType( 'value', 'eZ\\Publish\\Core\\Repository\\FieldType\\Rating\\Value' );
        }

        if ( !is_bool( $inputValue->isDisabled ) )
        {
            throw new InvalidArgumentValue( $inputValue, get_class( $this ) );
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
        return array(
            "sort_key_string" => "",
            "sort_key_int" => 0
        );
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Rating\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\Repository\FieldType\Rating\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return $value->isDisabled;
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
