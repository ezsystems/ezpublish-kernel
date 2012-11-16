<?php
/**
 * File containing the BinaryFile Type class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\BinaryFile;
use eZ\Publish\Core\FieldType\BinaryBase\Type as BaseType,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    eZ\Publish\SPI\Persistence\Content\FieldValue;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class Type extends BaseType
{
    /**
     * Return the field type identifier for this field type
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
     * @return Value
     */
    protected function createValue( array $inputValue )
    {
        return new Value( $inputValue );
    }

    /**
     * Implements the core of {@see acceptValue()}.
     *
     * @param mixed $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\BinaryFile\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
    {
        $inputValue = parent::internalAcceptValue( $inputValue );

        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\BinaryFile\\Value',
                $inputValue
            );
        }

        return $inputValue;
    }

    /**
     * Attempts to complete the data in $value
     *
     * @param Value $value
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
    public function toHash( $value )
    {
        $hash = parent::toHash( $value );

        if ( $hash === null )
        {
            return $hash;
        }

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
     * @return mixed
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        $result = parent::fromPersistenceValue( $fieldValue );

        if ( $result === null )
        {
            // empty value
            return null;
        }

        $result->downloadCount = ( isset( $fieldValue->externalData['downloadCount'] )
            ? $fieldValue->externalData['downloadCount']
            : 0 );

        return $result;
    }
}
