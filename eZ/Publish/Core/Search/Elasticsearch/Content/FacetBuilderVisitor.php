<?php

/**
 * File containing the Elasticsearch FacetBuilderVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Visits the facet builder tree into a hash representation of Elasticsearch aggregations.
 *
 * @deprecated
 */
abstract class FacetBuilderVisitor
{
    /**
     * Check if visitor is applicable to current facet result.
     *
     * @param string $name
     *
     * @return bool
     */
    abstract public function canMap($name);

    /**
     * Map Elasticsearch facet result back to facet objects.
     *
     * @param string $name
     * @param mixed $data
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\Facet
     */
    abstract public function map($name, $data);

    /**
     * Check if visitor is applicable to current facet builder.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder $facetBuilder
     *
     * @return bool
     */
    abstract public function canVisit(FacetBuilder $facetBuilder);

    /**
     * Map facet builder to a proper Elasticsearch representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder $facetBuilder
     *
     * @return mixed Hash representation of Elasticsearch aggregations
     */
    abstract public function visit(FacetBuilder $facetBuilder);

    /**
     * Map Elasticsearch return array into a sane hash map.
     *
     * @param mixed $data
     *
     * @return array
     */
    protected function mapData($data)
    {
        $values = [];

        foreach ($data->buckets as $bucket) {
            $values[$bucket->key] = $bucket->doc_count;
        }

        return $values;
    }
}
