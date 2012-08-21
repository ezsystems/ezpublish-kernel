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
     * Potentially builds and checks the type and structure of the $inputValue.
     *
     * This method first inspects $inputValue, if it needs to convert it, e.g.
     * into a dedicated value object. An example would be, that the field type
     * uses values of MyCustomFieldTypeValue, but can also accept strings as
     * the input. In that case, $inputValue first needs to be converted into a
     * MyCustomFieldTypeClass instance.
     *
     * After that, the (possibly converted) value is checked for structural
     * validity. Note that this does not include validation after the rules
     * from validators, but only plausibility checks for the general data
     * format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param mixed $inputValue
     *
     * @return mixed The potentially converted and structurally plausible value.
     */
    public function acceptValue( $inputValue )
    {
        $inputValue = parent::acceptValue( $inputValue );

        if ( $inputValue === null )
        {
            // Empty value
            return null;
        }

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
