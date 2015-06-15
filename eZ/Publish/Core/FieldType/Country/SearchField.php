<?php
/**
 * File containing the Country SearchField class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Country;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\FieldType\Indexable;
use eZ\Publish\SPI\Search;

/**
 * Indexable definition for Country field type
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
                'value',
                $field->value->data,
                new Search\FieldType\MultipleStringField()
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
            'value' => new Search\FieldType\MultipleStringField(),
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
        return "value";
    }
}
