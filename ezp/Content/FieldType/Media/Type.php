<?php
/**
 * File containing the Media Type class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Media;
use ezp\Content\FieldType,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Content\FieldType\Media\Value as MediaValue,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Io\BinaryFile;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = 'ezmedia';
    const IS_SEARCHABLE = false;

    protected $allowedValidators = array(
        'ezp\\Content\\FieldType\\BinaryFile\\FileSizeValidator'
    );

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \ezp\Content\FieldType\Media\Value
     */
    protected function getDefaultValue()
    {
        return new MediaValue;
    }

    /**
     * Checks if $inputValue can be parsed.
     * If the $inputValue actually can be parsed, the value is returned.
     * Otherwise, an \ezp\Base\Exception\BadFieldTypeInput exception is thrown
     *
     * @throws \ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param \ezp\Content\FieldType\Media\Value $inputValue
     * @return \ezp\Content\FieldType\Media\Value
     */
    protected function canParseValue( BaseValue $inputValue )
    {
        if ( !$inputValue instanceof MediaValue || !$inputValue->file instanceof BinaryFile )
        {
            throw new BadFieldTypeInput( $inputValue, get_class() );
        }
        return $inputValue;
    }

    /**
     * BinaryFile does not support sorting
     *
     * @return bool
     */
    protected function getSortInfo()
    {
        return false;
    }
}
