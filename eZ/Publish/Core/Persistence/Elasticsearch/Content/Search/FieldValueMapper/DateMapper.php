<?php
/**
 * File containing the DateMapper document field value mapper class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldValueMapper;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldValueMapper;
use eZ\Publish\SPI\Persistence\Content\Search\FieldType\DateField;
use eZ\Publish\SPI\Persistence\Content\Search\Field;
use DateTime;
use InvalidArgumentException;
use Exception;

/**
 * Maps DateField document field values to something Elasticsearch can index.
 */
class DateMapper extends FieldValueMapper
{
    /**
     * Check if field can be mapped
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Field $field
     *
     * @return mixed
     */
    public function canMap( Field $field )
    {
        return $field->type instanceof DateField;
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
        if ( is_numeric( $field->value ) )
        {
            $date = new DateTime( "@{$field->value}" );
        }
        else
        {
            try
            {
                $date = new DateTime( $field->value );
            }
            catch ( Exception $e )
            {
                throw new InvalidArgumentException( "Invalid date provided: " . $field->value );
            }
        }

        return $date->format( "Y-m-d\\TH:i:s\\Z" );
    }
}
