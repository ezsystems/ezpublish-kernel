<?php
/**
 * File containing the DateTime class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\DateAndTime;
use ezp\Content\FieldType,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Base\Exception\BadFieldTypeInput,
    DateTime;

class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = "ezdatetime";
    const IS_SEARCHABLE = true;

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \ezp\Content\FieldType\DateAndTime\Value
     */
    protected function getDefaultValue()
    {
        return new Value( new DateTime );
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
        $value = new DateTime( $inputValue );

        if ( !$value instanceof DateTime )
        {
            throw new BadFieldTypeInput( $inputValue, get_class( $this ) );
        }
        return $value;

    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @return array
     */
    protected function getSortInfo()
    {
        return array( 'sort_key_int' => $this->getValue()->getTimestamp() );
    }
}
