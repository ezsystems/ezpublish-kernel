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
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Persistence\Content\FieldValue;

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
     * Checks if value can be parsed.
     *
     * If the value actually can be parsed, the value is returned.
     *
     * @throws ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param mixed $inputValue
     * @return mixed
     */
    protected function canParseValue( $inputValue )
    {
        if ( ! is_string( $inputValue ) )
        {
            throw new BadFieldTypeInput( $inputValue, __CLASS__ );
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
        $this->value = $this->canParseValue( $inputValue );
    }

    /**
     * This field type does not have a handler object.
     *
     * @return void
     */
    public function getHandler()
    {
        return;
    }

    /**
     * Method to populate the FieldValue struct for field types
     *
     * @internal
     * @param \ezp\Persistence\Content\FieldValue $valueStruct The value struct which the field type data is packaged in for consumption by the storage engine.
     * @return void
     */
    public function setFieldValue( FieldValue $valueStruct )
    {
        $valueStruct->data = $this->getFieldTypeSettings() + $this->getValueData() + $this->getValidationData();
        $valueStruct->sortKey = $this->getSortInfo();
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @todo String normalization should occur here.
     * @return array
     */
    protected function getSortInfo()
    {
         return array( 'sort_key_string' => $this->value );
    }

    /**
     * Returns stored validation data in format suitable for packing it in a
     * FieldValue
     *
     * @return array
     */
    protected function getValidationData()
    {
        return array();
    }

    /**
     * Returns the value of the field type in a format suitable for packing it
     * in a FieldValue.
     *
     * @return array
     */
    protected function getValueData()
    {
        return array( 'value' => $this->value );
    }

}
