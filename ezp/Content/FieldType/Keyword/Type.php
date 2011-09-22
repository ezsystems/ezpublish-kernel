<?php
/**
 * File containing the Keyword field type
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Keyword;
use ezp\Content\FieldType,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Persistence\Content\FieldValue;

/**
 * Keyword field types
 *
 * Represents keywords.
 */
class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = "ezkeyword";
    const IS_SEARCHABLE = true;

    protected $allowedValidators = array();

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \ezp\Content\FieldType\Keyword\Value
     */
    protected function getDefaultValue()
    {
        return new Value( array() );
    }

    /**
     * Checks if value can be parsed.
     *
     * If the value actually can be parsed, the value is returned.
     *
     * @todo Implement it
     * @throws ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param mixed $inputValue
     * @return mixed
     */
    protected function canParseValue( BaseValue $inputValue )
    {
        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @todo Review this, created from copy/paste to unblock failing tests!
     * @return array
     */
    protected function getSortInfo()
    {
        return array( "sort_key_int" => $this->getValue() );
    }
}
