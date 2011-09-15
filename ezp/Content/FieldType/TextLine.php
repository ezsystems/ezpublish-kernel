<?php
/**
 * File containing the TextLine class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType;
use ezp\Content\FieldType,
    ezp\Content\FieldType\Value,
    ezp\Content\FieldType\TextLine\Value as TextLineValue,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Persistence\Content\PersistenceFieldValue,
    ezp\Content\Type\FieldDefinition;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class TextLine extends FieldType
{
    protected $fieldTypeString = 'ezstring';
    protected $isSearchable = true;

    /**
     * Default value
     * @var \ezp\Content\FieldType\TextLine\Value
     */
    protected $defaultValue;

    protected $allowedValidators = array( 'StringLengthValidator' );

    public function __construct()
    {
        parent::__construct();
        $this->defaultValue = new TextLineValue;
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
    protected function canParseValue( Value $inputValue )
    {
        if ( !$inputValue instanceof TextLineValue || !is_string( $inputValue->text ) )
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
         return array( 'sort_key_string' => $this->value->text );
    }

    /**
     * Returns the value of the field type in a format suitable for packing it
     * in a FieldValue.
     *
     * @return array
     */
    protected function getValueData()
    {
        return array( 'value' => $this->value->text );
    }
}
