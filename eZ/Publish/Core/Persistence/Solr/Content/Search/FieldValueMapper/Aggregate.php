<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\FieldValueMapper;

use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldValueMapper;
use eZ\Publish\SPI\Persistence\Content\Search\Field;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Maps raw document field values to something Solr can index.
 */
class Aggregate extends FieldValueMapper
{
    /**
     * Array of available mappers
     *
     * @var array
     */
    protected $mappers = array();

    /**
     * COnstruct from optional mapper array
     *
     * @param array $mappers
     *
     * @return void
     */
    public function __construct( array $mappers = array() )
    {
        foreach ( $mappers as $mapper )
        {
            $this->addMapper( $mapper );
        }
    }

    /**
     * Adds mapper
     *
     * @param FieldValueMapper $mapper
     *
     * @return void
     */
    public function addMapper( FieldValueMapper $mapper )
    {
        $this->mappers[] = $mapper;
    }

    /**
     * Check if field can be mapped
     *
     * @param Field $field
     *
     * @return void
     */
    public function canMap( Field $field )
    {
        return true;
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
        foreach ( $this->mappers as $mapper )
        {
            if ( $mapper->canMap( $field ) )
            {
                return $mapper->map( $field );
            }
        }

        throw new NotImplementedException( "No mapper available for: " . get_class( $field->type ) );
    }
}

