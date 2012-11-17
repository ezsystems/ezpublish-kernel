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
     * @return \eZ\Publish\Core\FieldType\Null\Type
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
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getName( $value )
    {
        $value = $this->acceptValue( $value );

        return (string)$value->value;
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Null\Value
     */
    public function getEmptyValue()
    {
        return new Value( null );
    }

    /**
     * Implements the core of {@see acceptValue()}.
     *
     * @param mixed $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Null\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
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
        if ( isset( $value->value ) )
        {
            return $value->value;
        }

        return null;
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
        if ( isset( $value->value ) )
        {
            return $value->value;
        }

        return null;
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
