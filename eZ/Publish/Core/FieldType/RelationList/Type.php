<?php
/**
 * File containing the RelationList FieldType class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RelationList;
use eZ\Publish\Core\FieldType\FieldType,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * The RelationList field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    protected $allowedValidators = array(
    //    'eZ\\Publish\\Core\\Repository\\FieldType\\RelationList\\StringLengthValidator'
    );

    /**
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $text as value.
     *
     * @param string $text
     * @return \eZ\Publish\Core\FieldType\RelationList\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $text )
    {
        return new Value( $text );
    }

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezobjectrelationlist";
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
        throw new \RuntimeException( '@todo Implement this method' );
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\RelationList\Value
     */
    public function getDefaultDefaultValue()
    {
        return new Value( '' );
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\FieldType\RelationList\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\RelationList\Value
     */
    public function acceptValue( $inputValue )
    {
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\Repository\\FieldType\\TextLine\\Value',
                $inputValue
            );
        }

        if ( !is_string( $inputValue->text ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->text',
                'string',
                $inputValue->text
            );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @todo String normalization should occur here.
     * @return array
     */
    protected function getSortInfo( $value )
    {
        return array( 'sort_key_string' => $value->text );
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\RelationList\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\RelationList\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return $value->text;
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
