<?php
/**
 * File containing the Author class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Author;
use eZ\Publish\Core\FieldType\FieldType,
    eZ\Publish\Core\Repository\Repository,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * Author field type.
 *
 * Field type representing a list of authors, consisting of author name, and
 * author email.
 */
class Type extends FieldType
{
    /**
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $authors as value.
     *
     * @param array $authors
     * @return \eZ\Publish\Core\FieldType\Author\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $authors )
    {
        return new Value( $authors );
    }

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezauthor";
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Author\Value
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
     * @param \eZ\Publish\Core\FieldType\Author\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Author\Value
     */
    public function acceptValue( $inputValue )
    {
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\Repository\\FieldType\\Author\\Value',
                $inputValue
            );
        }

        if ( !$inputValue->authors instanceof AuthorCollection )
        {
            throw new InvalidArgumentType(
                '$inputValue->authors',
                'eZ\\Publish\\Core\\Repository\\FieldType\\Author\\AuthorCollection',
                $inputValue->authors
            );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
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
     * @return \eZ\Publish\Core\FieldType\Author\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\Author\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return $value->authors;
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
