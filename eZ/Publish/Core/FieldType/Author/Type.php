<?php

/**
 * File containing the Author class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Author;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\SPI\FieldType\Value as SPIValue;

/**
 * Author field type.
 *
 * Field type representing a list of authors, consisting of author name, and
 * author email.
 */
class Type extends FieldType
{
    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezauthor';
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param \eZ\Publish\Core\FieldType\Author\Value $value
     *
     * @return string
     */
    public function getName(SPIValue $value)
    {
        return isset($value->authors[0]) ? $value->authors[0]->name : '';
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
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param array|\eZ\Publish\Core\FieldType\Author\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Author\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_array($inputValue)) {
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\Author\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if (!$value->authors instanceof AuthorCollection) {
            throw new InvalidArgumentType(
                '$value->authors',
                'eZ\\Publish\\Core\\FieldType\\Author\\AuthorCollection',
                $value->authors
            );
        }
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \eZ\Publish\Core\FieldType\Author\Value $value
     *
     * @return array
     */
    protected function getSortInfo(BaseValue $value)
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Author\Value $value
     */
    public function fromHash($hash)
    {
        return new Value(
            array_map(
                function ($author) {
                    return new Author($author);
                },
                $hash
            )
        );
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\Author\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        return array_map(
            function ($author) {
                return (array)$author;
            },
            $value->authors->getArrayCopy()
        );
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }
}
