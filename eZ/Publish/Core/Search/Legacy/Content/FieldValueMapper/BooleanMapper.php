<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\FieldValueMapper;

use eZ\Publish\Core\Search\Legacy\Content\FieldValueMapper;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;

/**
 * Maps raw document field values to something search engine can index.
 */
class BooleanMapper extends FieldValueMapper
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
        return $field->type instanceof FieldType\BooleanField;
    }

    /**
     * Map field value to a proper search engine representation.
     *
     * @param Field $field
     *
     * @return mixed
     */
    public function map(Field $field)
    {
        return $this->convert($field->value);
    }

    /**
     * Convert to a proper search engine representation.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function convert($value)
    {
        return (boolean) $value;
    }
}
