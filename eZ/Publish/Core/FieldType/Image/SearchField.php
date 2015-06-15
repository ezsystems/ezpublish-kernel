<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\FieldType\Indexable;
use eZ\Publish\SPI\Search;

/**
 * Indexable definition for TextLine field type
 */
class SearchField implements Indexable
{
    /**
     * Get index data for field for search backend
     *
     * @param Field $field
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    public function getIndexData( Field $field )
    {
        return array(
            new Search\Field(
                "filename",
                $field->value->data["fileName"],
                new Search\FieldType\StringField()
            ),
            new Search\Field(
                "alternative_text",
                $field->value->data["alternativeText"],
                new Search\FieldType\StringField()
            ),
            new Search\Field(
                "file_size",
                $field->value->data["fileSize"],
                new Search\FieldType\IntegerField()
            ),
            new Search\Field(
                'mime_type',
                $field->value->data["mime"],
                new Search\FieldType\StringField()
            ),
        );
    }

    /**
     * Get index field types for search backend
     *
     * @return \eZ\Publish\SPI\Search\FieldType[]
     */
    public function getIndexDefinition()
    {
        return array(
            "filename" => new Search\FieldType\StringField(),
            "alternative_text" => new Search\FieldType\StringField(),
            "file_size" => new Search\FieldType\IntegerField(),
            'mime_type' => new Search\FieldType\StringField(),
        );
    }

    /**
     * Get name of the default field to be used for query and sort.
     *
     * As field types can index multiple fields (see MapLocation field type's
     * implementation of this interface), this method is used to define default
     * field for query and sort. Default field is typically used by Field
     * criterion and sort clause.
     *
     * @return string
     */
    public function getDefaultField()
    {
        return "filename";
    }
}
