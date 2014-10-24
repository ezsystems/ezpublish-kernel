<?php
/**
 * File containing the MapLocation SearchField class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\MapLocation;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\FieldType\Indexable;
use eZ\Publish\SPI\Search;

/**
 * Indexable definition for MapLocation field type
 */
class SearchField implements Indexable
{
    /**
     * Get index data for field for search backend
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    public function getIndexData( Field $field )
    {
        return array(
            new Search\Field(
                'value_address',
                $field->value->externalData["address"],
                new Search\FieldType\StringField()
            ),
            new Search\Field(
                'value_location',
                array(
                    "latitude" => $field->value->externalData["latitude"],
                    "longitude" => $field->value->externalData["longitude"]
                ),
                new Search\FieldType\GeoLocationField()
            ),
        );
    }

    /**
     * Get index field types for search backend
     *
     * @return \eZ\Publish\SPI\Search\FieldType[]
     */
    public function getIndexDefinition()
    {
        return array(
            'value_address' => new Search\FieldType\StringField(),
            'value_location' => new Search\FieldType\GeoLocationField()
        );
    }
}
