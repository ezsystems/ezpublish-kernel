<?php
/**
 * File containing the Elasticsearch Section facet builder visitor class
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
 * Visits the Section facet builder
 */
class Section extends FacetBuilderVisitor
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
        return ( substr( $name, 0, 9 ) === "section__" );
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
        return new Facet\SectionFacet(
            array(
                "name" => (string)substr( $name, 9 ),
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
        return $facetBuilder instanceof FacetBuilder\SectionFacetBuilder;
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder $facetBuilder
     *
     * @return string
     */
    public function visit( FacetBuilder $facetBuilder )
    {
        return array(
            "section__{$facetBuilder->name}" => array(
                "terms" => array(
                    "field" => "section_id",
                    "min_doc_count" => $facetBuilder->minCount,
                    "size" => $facetBuilder->limit,
                )
            ),
        );
    }
}
