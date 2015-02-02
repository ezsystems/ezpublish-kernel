<?php
/**
 * File containing the MultipleBooleanMapper document field value mapper class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldValueMapper;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldValueMapper;
use eZ\Publish\SPI\Search\FieldType\MultipleBooleanField;
use eZ\Publish\SPI\Search\Field;

/**
 * Maps MultipleBooleanField document field values to something Elasticsearch can index.
 */
class MultipleBooleanMapper extends FieldValueMapper
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
        return $field->type instanceof MultipleBooleanField;
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed
     */
    public function map( Field $field )
    {
        $values = array();

        foreach ( $field->value as $value )
        {
            $values[] = (boolean)$value;
        }

        return $values;
    }
}
