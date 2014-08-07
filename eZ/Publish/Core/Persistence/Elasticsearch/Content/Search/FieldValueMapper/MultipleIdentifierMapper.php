<?php
/**
 * File containing the MultipleIdentifierMapper document field value mapper class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldValueMapper;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldValueMapper;
use eZ\Publish\SPI\Persistence\Content\Search\FieldType\MultipleIdentifierField;
use eZ\Publish\SPI\Persistence\Content\Search\Field;

/**
 * Maps MultipleIdentifierField document field values to something Elasticsearch can index.
 */
class MultipleIdentifierMapper extends IdentifierMapper
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
        return $field->type instanceof MultipleIdentifierField;
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
        $values = array();

        foreach ( $field->value as $value )
        {
            $values[] = $this->convert( $value );
        }

        return $values;
    }
}

