<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
     * @return void
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
     * @return void
     */
    public function map( Field $field )
    {
        // Remove non-printables

        return preg_replace( '([\x00-\x09\x0B\x0C\x1E\x1F]+)', '', $field->value instanceof DOMDocument ? $field->value->saveXML() : $field->value );
    }
}

