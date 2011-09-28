<?php
/**
 * File containing the TextLine class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\TextLine;
use ezp\Content\FieldType,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Base\Exception\BadFieldTypeInput;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = "ezstring";
    const IS_SEARCHABLE = true;

    protected $allowedValidators = array(
        'ezp\\Content\\FieldType\\TextLine\\StringLengthValidator'
    );

    protected $allowedSettings = array(
        'defaultText' => ''
    );

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \ezp\Content\FieldType\TextLine\Value
     */
    protected function getDefaultValue()
    {
        return new Value( $this->fieldSettings['defaultText'] );
    }

    /**
     * Checks if $inputValue can be parsed.
     * If the $inputValue actually can be parsed, the value is returned.
     * Otherwise, an \ezp\Base\Exception\BadFieldTypeInput exception is thrown
     *
     * @throws \ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param \ezp\Content\FieldType\TextLine\Value $inputValue
     * @return \ezp\Content\FieldType\TextLine\Value
     */
    protected function canParseValue( BaseValue $inputValue )
    {
        if ( !$inputValue instanceof Value || !is_string( $inputValue->text ) )
        {
            throw new BadFieldTypeInput( $inputValue, get_class() );
        }
        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @todo String normalization should occur here.
     * @return array
     */
    protected function getSortInfo()
    {
        return array( 'sort_key_string' => $this->getValue()->text );
    }
}
