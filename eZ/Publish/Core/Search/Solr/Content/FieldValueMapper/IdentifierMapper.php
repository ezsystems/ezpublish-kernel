<?php
/**
 * File containing the IdentifierMapper class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\FieldValueMapper;

use eZ\Publish\Core\Search\Solr\Content\FieldValueMapper;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;

/**
 * Maps raw document field values to something Solr can index.
 */
class IdentifierMapper extends FieldValueMapper
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
        return $field->type instanceof FieldType\IdentifierField;
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
        return preg_replace( '([^A-Za-z0-9/]+)', '', $value );
    }
}

