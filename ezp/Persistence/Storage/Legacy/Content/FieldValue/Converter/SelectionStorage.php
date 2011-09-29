<?php
/**
 * File containing the SelectionStorage Converter class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter;
use ezp\Persistence\Fields\Storage,
    ezp\Persistence\Content\Field;

/**
 * Converter for Selection field type external storage
 */
class SelectionStorage implements Storage
{
    const SELECTION_TABLE = "ezselection";

    /**
     * @see \ezp\Persistence\Fields\Storage
     */
    public function storeFieldData( Field $field, array $context )
    {
        /*
        @todo
        To store a selection:
        1. options must be retrieved
        2. options should be compared to create the option string to store:
          If, from a selection, only one option is selected, the index (starting from 0)
          is used as is.
          If, from a selection, several options are selected, the indexes are stored
          joined by a "-", eg: "0-2-8" for the 1st, 3rd and 9th options
        3. the option string must be stored in both data_text and sort_key_string
        */
    }

    /**
     * Populates $field value property based on the external data.
     * $field->value is a {@link ezp\Persistence\Content\FieldValue} object.
     * This value holds the data as a {@link ezp\Content\FieldType\Value} based object,
     * according to the field type (e.g. for TextLine, it will be a {@link ezp\Content\FieldType\TextLine\Value} object).
     *
     * @param \ezp\Persistence\Content\Field $field
     * @param array $context
     * @return void
     */
    public function getFieldData( Field $field, array $context )
    {
        /*
        @todo
        See storeFieldData()
        */
    }

    /**
     * @param array $fieldId
     * @param array $context
     * @return bool
     */
    public function deleteFieldData( array $fieldId, array $context )
    {
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return bool
     */
    public function hasFieldData()
    {
        return false;
    }

    /**
     * @param \ezp\Persistence\Content\Field $field
     * @param array $context
     */
    public function copyFieldData( Field $field, array $context )
    {
    }

    /**
     * @param \ezp\Persistence\Content\Field $field
     * @param array $context
     */
    public function getIndexData( Field $field, array $context )
    {
    }
}
