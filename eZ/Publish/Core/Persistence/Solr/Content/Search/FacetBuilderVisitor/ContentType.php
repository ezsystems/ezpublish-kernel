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
use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * Visits the ContentType facet builder
 */
class ContentType extends FacetBuilderVisitor
{
    /**
     * CHeck if visitor is applicable to current facet result
     *
     * @param string $field
     *
     * @return boolean
     */
    public function canMap( $field )
    {
        return $field === 'type_id';
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
        return new Facet\ContentTypeFacet(
            array(
                'name'    => 'type',
                'entries' => $this->mapData( $data ),
            )
        );
    }

    /**
     * Check if visitor is applicable to current facet builder
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return boolean
     */
    public function canVisit( FacetBuilder $facetBuilder )
    {
        return $facetBuilder instanceof FacetBuilder\ContentTypeFacetBuilder;
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param FacetBuilder $facetBuilder;
     *
     * @return void
     */
    public function visit( FacetBuilder $facetBuilder )
    {
        return http_build_query(
            array(
                'facet.field'             => 'type_id',
                'f.type_id.facet.limit'    => $facetBuilder->limit,
                'f.type_id.facet.mincount' => $facetBuilder->minCount,
            )
        );
    }
}

