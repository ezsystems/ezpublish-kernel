<?php
/**
 * File containing the GeoLocationMapper class
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
class GeoLocationMapper extends FieldValueMapper
{
    /**
     * Check if field can be mapped
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return boolean
     */
    public function canMap( Field $field )
    {
        return $field->type instanceof FieldType\GeoLocationField;
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed|null Returns null on empty value
     */
    public function map( Field $field )
    {
        if ( $field->value["latitude"] === null || $field->value["longitude"] === null )
            return null;

        return $field->value["latitude"] . "," . $field->value["longitude"];
    }
}
