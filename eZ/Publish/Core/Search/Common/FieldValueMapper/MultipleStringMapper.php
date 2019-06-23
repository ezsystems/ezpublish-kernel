<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\FieldValueMapper;

use eZ\Publish\SPI\Search\FieldType;
use eZ\Publish\SPI\Search\Field;

/**
 * Common multiple string field value mapper implementation.
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
            $field->type instanceof FieldType\TextField ||
            $field->type instanceof FieldType\FullTextField;
    }

    /**
     * Map field value to a proper search engine representation.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return array
     */
    public function map(Field $field)
    {
        $values = [];

        foreach ((array)$field->value as $value) {
            $values[] = $this->convert($value);
        }

        return $values;
    }
}
