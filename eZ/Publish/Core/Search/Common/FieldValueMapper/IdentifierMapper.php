<?php

/**
 * File containing the IdentifierMapper document field value mapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Common\FieldValueMapper;

use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\SPI\Search\FieldType\IdentifierField;
use eZ\Publish\SPI\Search\Field;

/**
 * Maps raw document field values to something Solr can index.
 */
class IdentifierMapper extends FieldValueMapper
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
        return $field->type instanceof IdentifierField;
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
        return $this->convert($field->value);
    }

    /**
     * Convert to a proper Solr representation.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function convert($value)
    {
        // Remove non-printable characters
        return preg_replace('([^A-Za-z0-9/]+)', '', $value);
    }
}
