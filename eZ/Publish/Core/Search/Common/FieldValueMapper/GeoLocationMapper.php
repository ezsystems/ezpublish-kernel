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

use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\SPI\Search\FieldType\GeoLocationField;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;

/**
 * Maps raw field values to something search engine can understand.
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
        if ($field->value['latitude'] === null || $field->value['longitude'] === null) {
            return null;
        }

        return sprintf('%F,%F', $field->value['latitude'], $field->value['longitude']);
    }
}
