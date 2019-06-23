<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Date;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\FieldType\Indexable;
use eZ\Publish\SPI\Search;
use DateTime;

/**
 * Indexable definition for Date field type.
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
        if ($field->value->data !== null) {
            $dateTime = new DateTime("@{$field->value->data['timestamp']}");
            $value = $dateTime->format('Y-m-d\\Z');
        } else {
            $value = null;
        }

        return [
            new Search\Field(
                'value',
                $value,
                new Search\FieldType\DateField()
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
            'value' => new Search\FieldType\DateField(),
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
        return 'value';
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
        return $this->getDefaultMatchField();
    }
}
