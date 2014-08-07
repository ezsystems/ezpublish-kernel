<?php
/**
 * File containing the Elasticsearch Aggregate facet builder visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FacetBuilderVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FacetBuilderVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use OutOfRangeException;

/**
 * Visits the facet builder tree into a Elasticsearch query
 * @todo find better method to map for type/name
 */
class Aggregate extends FacetBuilderVisitor
{
    /**
     * Array of available visitors
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FacetBuilderVisitor[]
     */
    protected $visitors = array();

    /**
     * Construct from optional visitor array
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FacetBuilderVisitor[] $visitors
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
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FacetBuilderVisitor $visitor
     */
    public function addVisitor( FacetBuilderVisitor $visitor )
    {
        $this->visitors[] = $visitor;
    }

    /**
     * CHeck if visitor is applicable to current facet result
     *
     * @param string $name
     *
     * @return boolean
     */
    public function canMap( $name )
    {
        return true;
    }

    /**
     * Map Elasticsearch facet result back to facet objects
     *
     * @param string $name
     * @param mixed $data
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\Facet
     */
    public function map( $name, $data )
    {
        foreach ( $this->visitors as $visitor )
        {
            if ( $visitor->canMap( $name ) )
            {
                return $visitor->map( $name, $data );
            }
        }

        throw new OutOfRangeException(
            "No visitor available for: " . $name
        );
    }

    /**
     * Check if visitor is applicable to current facet builder
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder $facetBuilder
     *
     * @return boolean
     */
    public function canVisit( FacetBuilder $facetBuilder )
    {
        return true;
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder $facetBuilder
     *
     * @return string
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

        throw new NotImplementedException(
            "No visitor available for: " . get_class( $facetBuilder )
        );
    }
}
