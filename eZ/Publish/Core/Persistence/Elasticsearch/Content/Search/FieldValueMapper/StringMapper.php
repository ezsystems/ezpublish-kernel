<?php
/**
 * File containing the StringMapper document field value mapper class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldValueMapper;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldValueMapper;
use eZ\Publish\SPI\Persistence\Content\Search\FieldType;
use eZ\Publish\SPI\Persistence\Content\Search\Field;
use DOMDocument;

/**
 * Maps raw document field values to something Elasticsearch can index.
 */
class StringMapper extends FieldValueMapper
{
    /**
     * Check if field can be mapped
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Field $field
     *
     * @return boolean
     */
    public function canMap( Field $field )
    {
        return
            $field->type instanceof FieldType\StringField ||
            $field->type instanceof FieldType\TextField;
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Field $field
     *
     * @return mixed
     */
    public function map( Field $field )
    {
        return $this->convert( $field->value );
    }

    /**
     * Convert to a proper Elasticsearch representation
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function convert( $value )
    {
        // Remove non-printable characters
        return preg_replace( '([\x00-\x09\x0B\x0C\x1E\x1F]+)', '', $value instanceof DOMDocument ? $value->saveXML() : $value );
    }
}

