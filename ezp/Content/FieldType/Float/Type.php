<?php
/**
 * File containing the Float class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Float;
use ezp\Content\FieldType,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Persistence\Content\FieldValue;

/**
 * Float field types
 *
 * Represents floats.
 */
class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = "ezfloat";
    const IS_SEARCHABLE = false;

    protected $allowedValidators = array( 'FloatValueValidator' );

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \ezp\Content\FieldType\Float\Value
     */
    protected function getDefaultValue()
    {
        return new Value( 0.0 );
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
        if ( !is_float( $inputValue->value ) )
        {
            throw new BadFieldTypeInput( $inputValue, get_class() );
        }
        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @return array
     */
    protected function getSortInfo()
    {
        return array(
            'sort_key_string' => '',
            'sort_key_int' => 0
        );
    }

    /**
     * Returns the value of the field type in a format suitable for packing it
     * in a FieldValue.
     *
     * @return array
     */
    protected function getValueData()
    {
        return array( 'value' => $this->getValue()->value );
    }

}
