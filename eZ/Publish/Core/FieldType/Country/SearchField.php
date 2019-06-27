<?php

/**
 * File containing the Country SearchField class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Country;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\FieldType\Indexable;
use eZ\Publish\SPI\Search;

/**
 * Indexable definition for Country field type.
 */
class SearchField implements Indexable
{
    /** @var array */
    protected $countriesInfo;

    /**
     * @param array $countriesInfo Array of countries data
     */
    public function __construct(array $countriesInfo)
    {
        $this->countriesInfo = $countriesInfo;
    }

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
        if (empty($field->value->data)) {
            return [];
        }

        $nameList = [];
        $IDCList = [];
        $alpha2List = [];
        $alpha3List = [];

        foreach ($field->value->data as $alpha2) {
            if (isset($this->countriesInfo[$alpha2])) {
                $nameList[] = $this->countriesInfo[$alpha2]['Name'];
                $IDCList[] = $this->countriesInfo[$alpha2]['IDC'];
                $alpha2List[] = $this->countriesInfo[$alpha2]['Alpha2'];
                $alpha3List[] = $this->countriesInfo[$alpha2]['Alpha3'];
            }
        }

        return [
            new Search\Field(
                'idc',
                $IDCList,
                new Search\FieldType\MultipleIntegerField()
            ),
            new Search\Field(
                'alpha2',
                $alpha2List,
                new Search\FieldType\MultipleStringField()
            ),
            new Search\Field(
                'alpha3',
                $alpha3List,
                new Search\FieldType\MultipleStringField()
            ),
            new Search\Field(
                'name',
                $nameList,
                new Search\FieldType\MultipleStringField()
            ),
            new Search\Field(
                'sort_value',
                $field->value->sortKey,
                new Search\FieldType\StringField()
            ),
            new Search\Field(
                'fulltext',
                $nameList,
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
            'idc' => new Search\FieldType\MultipleIntegerField(),
            'alpha2' => new Search\FieldType\MultipleStringField(),
            'alpha3' => new Search\FieldType\MultipleStringField(),
            'name' => new Search\FieldType\MultipleStringField(),
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
