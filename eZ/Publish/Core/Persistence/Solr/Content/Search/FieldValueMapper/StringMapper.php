<?php
/**
 * File containing the StringMapper class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\FieldValueMapper;

use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldValueMapper;
use eZ\Publish\SPI\Persistence\Content\Search\Field;
use eZ\Publish\SPI\Persistence\Content\Search\FieldType;
use DOMDocument;

/**
 * Maps raw document field values to something Solr can index.
 */
class StringMapper extends FieldValueMapper
{
    /**
     * Check if field can be mapped
     *
     * @param Field $field
     *
     * @return boolean
     */
    public function canMap( Field $field )
    {
        return
            $field->type instanceof FieldType\StringField ||
            $field->type instanceof FieldType\TextField ||
            $field->type instanceof FieldType\HtmlField;
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param Field $field
     *
     * @return mixed
     */
    public function map( Field $field )
    {
        return $this->convert( $field->value );
    }

    /**
     * Convert to a proper Solr representation
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function convert( $value )
    {
        // Remove non-printables
        return preg_replace( '([\x00-\x09\x0B\x0C\x1E\x1F]+)', '', $value instanceof DOMDocument ? $value->saveXML() : $value );
    }
}

