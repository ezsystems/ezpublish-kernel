<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\FieldValueMapper;

use eZ\Publish\SPI\Search\FieldType\MultipleIdentifierField;
use eZ\Publish\SPI\Search\Field;

/**
 * Common multiple identifier field value mapper implementation.
 */
class MultipleIdentifierMapper extends IdentifierMapper
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
        return $field->type instanceof MultipleIdentifierField;
    }

    /**
     * Map field value to a proper search engine representation.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed
     */
    public function map(Field $field)
    {
        $values = [];

        foreach ($field->value as $value) {
            if (!$field->type->raw) {
                $value = $this->convert($value);
            }

            $values[] = $value;
        }

        return $values;
    }
}
