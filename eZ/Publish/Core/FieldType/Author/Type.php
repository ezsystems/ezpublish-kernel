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
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezauthor";
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

        return isset( $value->authors[0] ) ? $value->authors[0]->name : "";
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Author\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Implements the core of {@see acceptValue()}.
     *
     * @param mixed $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Author\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
    {
        if ( is_array( $inputValue ) )
        {
            $inputValue = new Value( $inputValue );
        }
        else if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\Author\\Value',
                $inputValue
            );
        }

        if ( !$inputValue->authors instanceof AuthorCollection )
        {
            throw new InvalidArgumentType(
                '$inputValue->authors',
                'eZ\\Publish\\Core\\FieldType\\Author\\AuthorCollection',
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
        return new Value( array_map(
            function ( $author )
            {
                return new Author( $author );
            },
            $hash
        ) );
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
        return array_map(
            function ( $author )
            {
                return (array) $author;
            },
            $value->authors->getArrayCopy()
        );
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

    /**
     * Get index data for field data for search backend
     *
     * @param mixed $value
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Field[]
     */
    public function getIndexData( $value )
    {
        throw new \RuntimeException( '@TODO: Implement' );
    }
}
