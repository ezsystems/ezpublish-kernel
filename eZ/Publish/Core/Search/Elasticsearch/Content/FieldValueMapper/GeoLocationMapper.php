<?php

/**
 * File containing the GeoLocationMapper document field value mapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\FieldValueMapper;

use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\SPI\Search\FieldType\GeoLocationField;
use eZ\Publish\SPI\Search\Field;

/**
 * Maps raw document field values to something Elasticsearch can index.
 */
class GeoLocationMapper extends FieldValueMapper
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
        return $field->type instanceof GeoLocationField;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed|null Returns null on empty value
     */
    public function map(Field $field)
    {
        if (!isset($field->value['latitude'], $field->value['longitude'])) {
            return null;
        }

        return [
            'lat' => $field->value['latitude'],
            'lon' => $field->value['longitude'],
        ];
    }
}
