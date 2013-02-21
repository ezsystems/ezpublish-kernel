<?php
/**
 * File containing the MultipleIdentifierMapper class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\FieldValueMapper;

use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldValueMapper;
use eZ\Publish\SPI\Persistence\Content\Search\Field;
use eZ\Publish\SPI\Persistence\Content\Search\FieldType;

/**
 * Maps raw document field values to something Solr can index.
 */
class MultipleIdentifierMapper extends IdentifierMapper
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
        return $field->type instanceof FieldType\MultipleIdentifierField;
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
        $values = array();

        foreach ( $field->value as $value )
        {
            $values[] = $this->convert( $value );
        }

        return $values;
    }
}

