<?php
/**
 * File containing the Elasticsearch FacetBuilderVisitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Visits the facet builder tree into a Elasticsearch query
 */
abstract class FacetBuilderVisitor
{
    /**
     * Check if visitor is applicable to current facet result
     *
     * @param string $name
     *
     * @return boolean
     */
    abstract public function canMap( $name );

    /**
     * Map Elasticsearch facet result back to facet objects
     *
     * @param string $name
     * @param mixed $data
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\Facet
     */
    abstract public function map( $name, $data );

    /**
     * Check if visitor is applicable to current facet builder
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder $facetBuilder
     *
     * @return boolean
     */
    abstract public function canVisit( FacetBuilder $facetBuilder );

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder $facetBuilder
     *
     * @return string
     */
    abstract public function visit( FacetBuilder $facetBuilder );

    /**
     * Map Elasticsearch return array into a sane hash map
     *
     * @param mixed $data
     *
     * @return array
     */
    protected function mapData( $data )
    {
        $values = array();

        foreach ( $data->buckets as $bucket )
        {
            $values[$bucket->key] = $bucket->doc_count;
        }

        return $values;
    }
}

