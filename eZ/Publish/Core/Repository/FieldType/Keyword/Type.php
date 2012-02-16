<?php
/**
 * File containing the Keyword field type
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Keyword;
use eZ\Publish\Core\Repository\FieldType,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    ezp\Base\Exception\BadFieldTypeInput;

/**
 * Keyword field types
 *
 * Represents keywords.
 */
class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = "ezkeyword";
    const IS_SEARCHABLE = true;

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Keyword\Value
     */
    public function getDefaultValue()
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
     *       According to me (PA) sorting on keywords should not be supported.
     * @return array
     */
    protected function getSortInfo( BaseValue $value )
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash );
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
        return $value->values;
    }
}
