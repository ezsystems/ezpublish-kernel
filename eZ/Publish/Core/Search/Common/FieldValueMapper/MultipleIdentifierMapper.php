<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Common\FieldValueMapper;

use eZ\Publish\SPI\Search\FieldType\MultipleIdentifierField;
use eZ\Publish\SPI\Search\Field;

/**
 * Maps raw field values to something search engine can understand.
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
     * Map field value to a proper Solr representation.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed
     */
    public function map(Field $field)
    {
        $values = array();

        foreach ($field->value as $value) {
            $values[] = $this->convert($value);
        }

        return $values;
    }
}
