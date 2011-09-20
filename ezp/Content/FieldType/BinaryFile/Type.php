<?php
/**
 * File containing the TextLine class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\BinaryFile;
use ezp\Content\FieldType,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Content\FieldType\BinaryFile\Value as BinaryFileValue,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Content\Type\FieldDefinition,
    ezp\Io\BinaryFile;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = "ezbinaryfile";
    const IS_SEARCHABLE = true;

    protected $allowedValidators = array( 'FileSizeValidator' );

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \ezp\Content\FieldType\BinaryFile\Value
     */
    protected function getDefaultValue()
    {
        return new BinaryFileValue;
    }

    /**
     * Checks if $inputValue can be parsed.
     * If the $inputValue actually can be parsed, the value is returned.
     * Otherwise, an \ezp\Base\Exception\BadFieldTypeInput exception is thrown
     *
     * @throws \ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param \ezp\Content\FieldType\BinaryFile\Value $inputValue
     * @return \ezp\Content\FieldType\BinaryFile\Value
     */
    protected function canParseValue( BaseValue $inputValue )
    {
        if ( !$inputValue instanceof BinaryFileValue || !$inputValue->file instanceof BinaryFile )
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

    /**
     * Returns the value of the field type in a format suitable for packing it
     * in a FieldValue.
     *
     * @return array
     */
    protected function getValueData()
    {
        return array(
            'file' => $this->getValue()->file,
            'originalFilename' => $this->getValue()->originalFilename
        );
    }
}
