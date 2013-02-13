<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Visits the facet builder tree into a Solr query
 */
abstract class FacetBuilderVisitor
{
    /**
     * CHeck if visitor is applicable to current facet result
     *
     * @param string $field
     *
     * @return boolean
     */
    abstract public function canMap( $field );

    /**
     * Map Solr facet result back to facet objects
     *
     * @param string $field
     * @param array $data
     *
     * @return Facet
     */
    abstract public function map( $field, array $data );

    /**
     * CHeck if visitor is applicable to current facet builder
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return boolean
     */
    abstract public function canVisit( FacetBuilder $facetBuilder );

    /**
     * Map field value to a proper Solr representation
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return void
     */
    abstract public function visit( FacetBuilder $facetBuilder );

    /**
     * Map Solr return array into a sane hash map
     *
     * @param array $data
     *
     * @return array
     */
    protected function mapData( array $data )
    {
        $values = array();
        reset( $data );
        while ( $key = current( $data ) )
        {
            $values[$key] = next( $data );
            next( $data );
        }

        return $values;
    }
}

