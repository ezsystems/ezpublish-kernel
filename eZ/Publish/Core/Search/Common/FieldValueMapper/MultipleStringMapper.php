<?php

/**
 * File containing the MultipleStringMapper document field value mapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Common\FieldValueMapper;

use eZ\Publish\SPI\Search\FieldType;
use eZ\Publish\SPI\Search\Field;

/**
 * Maps raw document field values to something Solr can index.
 */
class MultipleStringMapper extends StringMapper
{
    /**
     * Check if field can be mapped.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return bool
     */
    public function canMap(Field $field)
    {
        return
            $field->type instanceof FieldType\MultipleStringField ||
            $field->type instanceof FieldType\FullTextField;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
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
