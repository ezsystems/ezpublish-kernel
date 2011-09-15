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

    protected $defaultValue = "";

    protected $allowedValidators = array();

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
     * Method to populate the FieldValue struct for field types.
     *
     * This method is used by the business layer to populate the value object
     * for field type data.
     *
     * @internal
     * @param \ezp\Persistence\Content\FieldValue $valueStruct The value struct which the field type data is packaged in for consumption by the storage engine.
     * @return void
     */
    public function setFieldValue( FieldValue $valueStruct )
    {
        $valueStruct->data = $this->getFieldTypeSettings() + $this->getValueData();
        $valueStruct->sortKey = $this->getSortInfo();
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @todo Review this, created from copy/paste to unblock failing tests!
     * @return array
     */
    protected function getSortInfo()
    {
        return array( "sort_key_int" => $this->value );
    }

    /**
     * Returns the value of the field type in a format suitable for packing it
     * in a FieldValue.
     *
     * @todo Review this, created from copy/paste to unblock failing tests!
     * @return array
     */
    protected function getValueData()
    {
        return array( "value" => $this->value );
    }
}
