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
 * Maps FloatField document field values to something legacy search engine can index.
 */
class FloatMapper extends FieldValueMapper
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
        return $field->type instanceof FieldType\FloatField;
    }

    /**
     * Map field value to a proper legacy search engine representation.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed
     */
    public function map(Field $field)
    {
        return (float) $field->value;
    }
}
