<?php
/**
 * File containing the Checkbox class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Checkbox;
use ezp\Content\FieldType,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Base\Exception\InvalidArgumentType;

/**
 * Checkbox field type.
 *
 * Represent boolean values.
 */
class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = "ezboolean";
    const IS_SEARCHABLE = true;

    protected $allowedSettings = array(
        'defaultValue' => false
    );

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \ezp\Content\FieldType\Checkbox\Value
     */
    protected function getDefaultValue()
    {
        return new Value( $this->fieldSettings['defaultValue'] );
    }

    /**
     * Checks if value can be parsed.
     *
     * If the value actually can be parsed, the value is returned.
     *
     * @throws \ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @throws \ezp\Base\Exception\InvalidArgumentType
     * @param \ezp\Content\FieldType\Value $inputValue
     * @return \ezp\Content\FieldType\Checkbox\Value
     */
    protected function canParseValue( BaseValue $inputValue )
    {
        if ( $inputValue instanceof Value )
        {
            if ( !is_bool( $inputValue->bool ) )
                throw new BadFieldTypeInput( $inputValue, get_class( $this ) );

            return $inputValue;
        }

        throw new InvalidArgumentType( 'value', 'ezp\\Content\\FieldType\\Checkbox\\Value' );
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @return array
     */
    protected function getSortInfo()
    {
        return array( 'sort_key_int' => (int)$this->getValue()->bool );
    }
}
