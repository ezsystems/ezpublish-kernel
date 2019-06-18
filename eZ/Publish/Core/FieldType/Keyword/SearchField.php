<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Keyword;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\FieldType\Indexable;
use eZ\Publish\SPI\Search;

/**
 * Indexable definition for Keyword field type.
 */
class SearchField implements Indexable
{
    public function getIndexData(Field $field, FieldDefinition $fieldDefinition)
    {
        return [
            new Search\Field(
                'value',
                $field->value->externalData,
                new Search\FieldType\MultipleStringField()
            ),
            new Search\Field(
                'sort_value',
                implode(' ', $field->value->externalData),
                new Search\FieldType\StringField()
            ),
            new Search\Field(
                'fulltext',
                $field->value->externalData,
                new Search\FieldType\FullTextField()
            ),
        ];
    }

    public function getIndexDefinition()
    {
        return [
            'value' => new Search\FieldType\MultipleStringField(),
            'sort_value' => new Search\FieldType\StringField(),
        ];
    }

    public function getDefaultMatchField()
    {
        return 'value';
    }

    public function getDefaultSortField()
    {
        return 'sort_value';
    }
}
