<?php
/**
 * File containing the MultipleStringMapper document field value mapper class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldValueMapper;

use eZ\Publish\SPI\Persistence\Content\Search\FieldType\MultipleStringField;
use eZ\Publish\SPI\Persistence\Content\Search\Field;

/**
 * Maps MultipleStringField document field values to something Elasticsearch can index.
 */
class MultipleStringMapper extends StringMapper
{
    /**
     * Check if field can be mapped
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Field $field
     *
     * @return bool
     */
    public function canMap( Field $field )
    {
        return $field->type instanceof MultipleStringField;
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Field $field
     *
     * @return array
     */
    public function map( Field $field )
    {
        $values = array();

        foreach ( (array)$field->value as $value )
        {
            $values[] = $this->convert( $value );
        }

        return $values;
    }
}

