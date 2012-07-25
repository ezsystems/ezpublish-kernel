<?php
/**
 * File containing the Float class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Float;
use eZ\Publish\Core\FieldType\FieldType,
    eZ\Publish\Core\Repository\ValidatorService,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    eZ\Publish\Core\FieldType\ValidationError;

/**
 * Float field types
 *
 * Represents floats.
 */
class Type extends FieldType
{
    protected $allowedValidators = array(
        "FloatValueValidator"
    );

    /**
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $value as value.
     *
     * @param float $value
     * @return \eZ\Publish\Core\FieldType\Float\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $value )
    {
        return new Value( $value );
    }

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezfloat';
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Float\Value
     */
    public function getDefaultDefaultValue()
    {
        return new Value();
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\FieldType\Float\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Float\Value
     */
    public function acceptValue( $inputValue )
    {
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\Float\\Value',
                $inputValue
            );
        }

        if ( !is_float( $inputValue->value ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->value',
                'float',
                $inputValue->value
            );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @todo Sort seems to not be supported by this FieldType, is this handled correctly?
     * @return array
     */
    protected function getSortInfo( $value )
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Float\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\Float\Value $value
     *
     * @return mixed
     */
    public function toHash( Value $value )
    {
        return $value->value;
    }
}
