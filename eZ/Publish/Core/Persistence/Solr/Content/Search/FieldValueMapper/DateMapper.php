<?php
/**
 * File containing the DateMapper class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\FieldValueMapper;

use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldValueMapper;
use eZ\Publish\SPI\Persistence\Content\Search\Field;
use eZ\Publish\SPI\Persistence\Content\Search\FieldType;
use DateTime;
use InvalidArgumentException;
use Exception;

/**
 * Maps raw document field values to something Solr can index.
 */
class DateMapper extends FieldValueMapper
{
    /**
     * Check if field can be mapped
     *
     * @param Field $field
     *
     * @return mixed
     */
    public function canMap( Field $field )
    {
        return $field->type instanceof FieldType\DateField;
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
