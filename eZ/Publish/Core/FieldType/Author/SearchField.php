<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Author;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\FieldType\Indexable;
use eZ\Publish\SPI\Search;

/**
 * Indexable definition for Author field type.
 */
class SearchField implements Indexable
{
    /**
     * Get index data for field for search backend.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    public function getIndexData(Field $field, FieldDefinition $fieldDefinition)
    {
        $name = [];
        $id = [];
        $email = [];

        foreach ($field->value->data as $author) {
            $name[] = $author['name'];
            $id[] = $author['id'];
            $email[] = $author['email'];
        }

        return [
            new Search\Field(
                'name',
                $name,
                new Search\FieldType\MultipleStringField()
            ),
            new Search\Field(
                'id',
                $id,
                new Search\FieldType\MultipleIntegerField()
            ),
            new Search\Field(
                'email',
                $email,
                new Search\FieldType\MultipleStringField()
            ),
            new Search\Field(
                'count',
                count($field->value->data),
                new Search\FieldType\IntegerField()
            ),
            new Search\Field(
                'sort_value',
                implode('-', $name),
                new Search\FieldType\StringField()
            ),
            new Search\Field(
                'fulltext',
                $name,
                new Search\FieldType\FullTextField()
            ),
        ];
    }

    /**
     * Get index field types for search backend.
     *
     * @return \eZ\Publish\SPI\Search\FieldType[]
     */
    public function getIndexDefinition()
    {
        return [
            'name' => new Search\FieldType\MultipleStringField(),
            'id' => new Search\FieldType\MultipleIntegerField(),
            'email' => new Search\FieldType\MultipleStringField(),
            'count' => new Search\FieldType\IntegerField(),
            'sort_value' => new Search\FieldType\StringField(),
        ];
    }

    /**
     * Get name of the default field to be used for matching.
     *
     * As field types can index multiple fields (see MapLocation field type's
     * implementation of this interface), this method is used to define default
     * field for matching. Default field is typically used by Field criterion.
     *
     * @return string
     */
    public function getDefaultMatchField()
    {
        return 'name';
    }

    /**
     * Get name of the default field to be used for sorting.
     *
     * As field types can index multiple fields (see MapLocation field type's
     * implementation of this interface), this method is used to define default
     * field for sorting. Default field is typically used by Field sort clause.
     *
     * @return string
     */
    public function getDefaultSortField()
    {
        return 'sort_value';
    }
}
