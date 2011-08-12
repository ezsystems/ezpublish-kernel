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
    ezp\Base\Exception\BadFieldTypeInput;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class TextLine extends FieldType
{
    protected $fieldTypeString = 'ezstring';
    protected $defaultValue = '';
    protected $isSearchable = true;
    protected $isTranslateable = true;

    protected $allowedSettings = array( 'maxStringLength' => null );

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Parses value given to field type.
     *
     * This method will read input data, and convert it to the internal format.
     *
     * This method will throw an exception if the input data is not recognized.
     *
     * @throws ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param mixed $inputValue
     * @return mixed
     */
    protected function parseValue( $inputValue )
    {
        if ( ! is_string( $inputValue ) )
        {
            throw new BadFieldTypeInput( 'inputValue', $inputValue, __CLASS__ );
        }
        return $inputValue;
    }

    /**
     * Sets the value of a field type.
     *
     * @param string $inputValue
     * @return void
     */
    public function setValue( $inputValue )
    {
        $this->inputValue = $this->parseValue( $inputValue );
    }

    /**
     * Returns the string value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->inputValue;
    }

    /**
     * This field type does not have a handler object.
     *
     * @return void
     */
    public function getTypeHandler()
    {
        return;
    }
}
