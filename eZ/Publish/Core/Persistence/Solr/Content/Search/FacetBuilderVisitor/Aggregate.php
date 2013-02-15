<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\FacetBuilderVisitor;

use eZ\Publish\Core\Persistence\Solr\Content\Search\FacetBuilderVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Visits the facet builder tree into a Solr query
 */
class Aggregate extends FacetBuilderVisitor
{
    /**
     * Array of available visitors
     *
     * @var array
     */
    protected $visitors = array();

    /**
     * COnstruct from optional visitor array
     *
     * @param array $visitors
     *
     * @return void
     */
    public function __construct( array $visitors = array() )
    {
        foreach ( $visitors as $visitor )
        {
            $this->addVisitor( $visitor );
        }
    }

    /**
     * Adds visitor
     *
     * @param FieldValueVisitor $visitor
     *
     * @return void
     */
    public function addVisitor( FacetBuilderVisitor $visitor )
    {
        $this->visitors[] = $visitor;
    }

    /**
     * CHeck if visitor is applicable to current facet result
     *
     * @param string $field
     *
     * @return boolean
     */
    public function canMap( $field )
    {
        return true;
    }

    /**
     * Map Solr facet result back to facet objects
     *
     * @param string $field
     * @param array $data
     *
     * @return Facet
     */
    public function map( $field, array $data )
    {
        foreach ( $this->visitors as $visitor )
        {
            if ( $visitor->canMap( $field ) )
            {
                return $visitor->map( $field, $data );
            }
        }

        throw new \OutOfRangeException( "No visitor available for: " . $field );
    }

    /**
     * CHeck if visitor is applicable to current facet builder
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return boolean
     */
    public function canVisit( FacetBuilder $facetBuilder )
    {
        return true;
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return void
     */
    public function visit( FacetBuilder $facetBuilder )
    {
        foreach ( $this->visitors as $visitor )
        {
            if ( $visitor->canVisit( $facetBuilder ) )
            {
                return $visitor->visit( $facetBuilder );
            }
        }

        throw new NotImplementedException( "No visitor available for: " . get_class( $facetBuilder ) );
    }
}

