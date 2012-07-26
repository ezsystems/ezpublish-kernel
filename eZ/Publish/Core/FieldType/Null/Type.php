<?php
/**
 * File containing the Null field type
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Null;
use eZ\Publish\Core\FieldType\FieldType;

/**
 * ATTENTION: For testing purposes only!
 */
class Type extends FieldType
{
    /**
     * Identifier for the field type this stuff is mocking
     *
     * @var string
     */
    protected $fieldTypeIdentifier;

    /**
     * Constructs field type object, initializing internal data structures.
     *
     * @param string $fieldTypeIdentifier
     * @return void
     */
    public function __construct( $fieldTypeIdentifier )
    {
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
    }

    /**
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $value as value.
     *
     * @param int $value
     * @return \eZ\Publish\Core\FieldType\Null\Value
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
        return $this->fieldTypeIdentifier;
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Null\Value
     */
    public function getDefaultDefaultValue()
    {
        return new Value( null );
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\FieldType\Null\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Null\Value
     */
    public function acceptValue( $inputValue )
    {
        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @return array
     */
    protected function getSortInfo( $value )
    {
        return $value->value;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Null\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\Null\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return $value->value;
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }
}
