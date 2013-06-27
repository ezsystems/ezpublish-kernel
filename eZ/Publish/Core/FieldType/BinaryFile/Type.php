<?php
/**
 * File containing the BinaryFile Type class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\BinaryFile;

use eZ\Publish\Core\FieldType\BinaryBase\Type as BinaryBaseType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\Core\FieldType\BinaryBase\Value as BinaryBaseValue;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class Type extends BinaryBaseType
{
    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezbinaryfile";
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\BinaryFile\Value
     */
    public function getEmptyValue()
    {
        return new Value;
    }

    /**
     * Creates a specific value of the derived class from $inputValue
     *
     * @param array $inputValue
     *
     * @return Value
     */
    protected function createValue( array $inputValue )
    {
        return new Value( $inputValue );
    }

    /**
     * Throws an exception if the given $value is not an instance of the supported value subtype.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the parameter is not an instance of the supported value subtype.
     *
     * @param mixed $value A value returned by {@see createValueFromInput()}.
     *
     * @return void
     */
    protected function checkValueType( $value )
    {
        if ( !$value instanceof Value )
        {
            throw new InvalidArgumentType(
                '$value',
                'eZ\\Publish\\Core\\FieldType\\BinaryFile\\Value',
                $value
            );
        }
    }

    /**
     * Attempts to complete the data in $value
     *
     * @param mixed $value
     *
     * @return void
     */
    protected function completeValue( $value )
    {
        parent::completeValue( $value );

        if ( !isset( $value->downloadCount ) )
        {
            $value->downloadCount = 0;
        }
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\BinaryFile\Value $value
     *
     * @return mixed
     */
    public function toHash( SPIValue $value )
    {
        if ( $this->isEmptyValue( $value ) )
        {
            return null;
        }

        $hash = parent::toHash( $value );

        $hash['downloadCount'] = $value->downloadCount;

        return $hash;
    }

    /**
     * Converts a persistence $fieldValue to a Value
     *
     * This method builds a field type value from the $data and $externalData properties.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \eZ\Publish\Core\FieldType\BinaryFile\Value
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        if ( $fieldValue->externalData === null )
        {
            return $this->getEmptyValue();
        }

        $result = parent::fromPersistenceValue( $fieldValue );

        $result->downloadCount = ( isset( $fieldValue->externalData['downloadCount'] )
            ? $fieldValue->externalData['downloadCount']
            : 0 );

        return $result;
    }
}
