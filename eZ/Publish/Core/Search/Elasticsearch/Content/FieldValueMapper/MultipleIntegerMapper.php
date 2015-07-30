<?php

/**
 * File containing the MultipleStringMapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\FieldValueMapper;

use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType\MultipleIntegerField;

/**
 * Maps raw document field values to something Solr can index.
 */
class MultipleIntegerMapper extends IntegerMapper
{
    /**
     * Check if field can be mapped.
     *
     * @param Field $field
     *
     * @return bool
     */
    public function canMap(Field $field)
    {
        return $field->type instanceof MultipleIntegerField;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param Field $field
     *
     * @return array
     */
    public function map(Field $field)
    {
        $values = array();

        foreach ((array)$field->value as $value) {
            $values[] = $this->convert($value);
        }

        return $values;
    }
}
