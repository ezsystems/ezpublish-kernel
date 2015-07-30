<?php

/**
 * File containing the IntegerMapper document field value mapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\FieldValueMapper;

use eZ\Publish\Core\Search\Elasticsearch\Content\FieldValueMapper;
use eZ\Publish\SPI\Search\FieldType\IntegerField;
use eZ\Publish\SPI\Search\Field;

/**
 * Maps IntegerField document field values to something Elasticsearch can index.
 */
class IntegerMapper extends FieldValueMapper
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
        return $field->type instanceof IntegerField;
    }

    /**
     * Map field value to a proper Elasticsearch representation.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed
     */
    public function map(Field $field)
    {
        return $this->convert($field->value);
    }

    /**
     * Convert to a proper Elasticsearch representation.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function convert($value)
    {
        return (int)$value;
    }
}
