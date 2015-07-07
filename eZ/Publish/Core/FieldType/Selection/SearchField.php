<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Selection;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\FieldType\Indexable;
use eZ\Publish\SPI\Search;

/**
 * Indexable definition for Selection field type
 */
class SearchField implements Indexable
{
    /**
     * Get index data for field for search backend
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    public function getIndexData( Field $field, FieldDefinition $fieldDefinition )
    {
        $indexes = array();
        $values = array();
        $fieldSettings = $fieldDefinition->fieldTypeConstraints->fieldSettings;
        $options = $fieldSettings["options"];
        $positionSet = array_flip( $field->value->data );

        foreach ( $options as $index => $value )
        {
            if ( isset( $positionSet[$index] ) )
            {
                $values[] = $value;
                $indexes[] = $index;
            }
        }

        return array(
            new Search\Field(
                'option_value',
                $values,
                new Search\FieldType\MultipleStringField()
            ),
            new Search\Field(
                'option_index',
                $indexes,
                new Search\FieldType\MultipleIntegerField()
            ),
            new Search\Field(
                'option_count',
                count( $indexes ),
                new Search\FieldType\IntegerField()
            ),
            new Search\Field(
                'sort_value',
                implode( "-", $indexes ),
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
            'option_value' => new Search\FieldType\MultipleStringField(),
            'option_index' => new Search\FieldType\MultipleIntegerField(),
            'sort_value' => new Search\FieldType\StringField(),
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
    public function getDefaultMatchField()
    {
        return "option_index";
    }

    public function getDefaultSortField()
    {
        return "sort_value";
    }
}
