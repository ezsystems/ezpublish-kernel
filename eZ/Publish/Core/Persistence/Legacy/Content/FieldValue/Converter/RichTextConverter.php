<?php

/**
 * File containing the RichText converter.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\FieldType\RichText\Value;

class RichTextConverter implements Converter
{
    /**
     * Factory for current class.
     *
     * Note: Class should instead be configured as service if it gains dependencies.
     *
     * @deprecated since 6.8, will be removed in 7.x, use default constructor instead.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\RichTextConverter
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Converts data from $value to $storageFieldValue.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
        $storageFieldValue->dataText = $value->data;
        $storageFieldValue->sortKeyString = $value->sortKey;
    }

    /**
     * Converts data from $value to $fieldValue.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        $fieldValue->data = $value->dataText ?: Value::EMPTY_VALUE;
        $fieldValue->sortKey = $value->sortKeyString;
    }

    /**
     * Converts field definition data from $fieldDefinition into $storageFieldDefinition.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDefinition
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDefinition, StorageFieldDefinition $storageDefinition)
    {
        // Nothing to store
    }

    /**
     * Converts field definition data from $storageDefinition into $fieldDefinition.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDefinition
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDefinition, FieldDefinition $fieldDefinition)
    {
        $fieldDefinition->defaultValue->data = Value::EMPTY_VALUE;
    }

    /**
     * Returns the name of the index column in the attribute table.
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return string|false
     */
    public function getIndexColumn()
    {
        return 'sort_key_string';
    }
}
