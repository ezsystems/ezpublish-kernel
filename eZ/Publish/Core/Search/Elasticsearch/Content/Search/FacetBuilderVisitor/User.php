<?php
/**
 * File containing the Elasticsearch User facet builder visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FacetBuilderVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FacetBuilderVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * Visits the User facet builder
 */
class User extends FacetBuilderVisitor
{
    /**
     * Check if visitor is applicable to current facet result
     *
     * @param string $name
     *
     * @return boolean
     */
    public function canMap( $name )
    {
        return ( substr( $name, 0, 6 ) === "user__" );
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
        return new Facet\UserFacet(
            array(
                "name" => (string)substr( $name, 6 ),
                "entries" => $this->mapData( $data ),
            )
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
        return $facetBuilder instanceof FacetBuilder\UserFacetBuilder;
    }

    /**
     * Map facet builder to a proper Elasticsearch representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder $facetBuilder
     *
     * @return mixed
     */
    public function visit( FacetBuilder $facetBuilder )
    {
        return array(
            "user__{$facetBuilder->name}" => array(
                "terms" => array(
                    "field" => "creator_id",
                    "min_doc_count" => $facetBuilder->minCount,
                    "size" => $facetBuilder->limit,
                )
            ),
        );
    }
}
