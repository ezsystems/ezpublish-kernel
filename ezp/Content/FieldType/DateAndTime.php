<?php
/**
 * File containing the DateTime class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType;
use ezp\Content\FieldType,
    \ezp\Base\Exception\BadFieldTypeInput,
    \ezp\Persistence\Content\FieldValue,
    DateTime;

class DateAndTime extends FieldType
{
    protected $fieldTypeString = 'ezdatetime';
    protected $defaultValue = null;
    protected $isSearchable = true;

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
        $value = new DateTime( $inputValue );

        if ( !$value instanceof DateTime )
        {
            throw new BadFieldTypeInput( $inputValue, __CLASS__ );
        }
        return $value;

    }

    /**
     * Sets the value of a field type.
     *
     * @param $inputValue
     * @return void
     */
    public function setValue( $inputValue )
    {
        $this->value = $this->canParseValue( $inputValue );
    }

    /**
     * Returns a handler, aka. a helper object which aids in the manipulation of
     * complex field type values.
     *
     * @return void|ezp\Content\FieldType\Handler
     */
    public function getHandler()
    {
        return;
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
        $valueStruct->data = $this->getValueData();
        $valueStruct->sortKey = $this->getSortInfo();
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @return array
     */
    protected function getSortInfo()
    {
        return array( 'sort_key_int' => $this->value->getTimestamp() );
    }

    /**
     * Returns the value of the field type in a format suitable for packing it
     * in a FieldValue.
     *
     * @return array
     */
    protected function getValueData()
    {
        return array( 'value' => $this->value->getTimestamp() );
    }


}
