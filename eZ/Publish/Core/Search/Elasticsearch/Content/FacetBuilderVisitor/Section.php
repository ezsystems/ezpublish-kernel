<?php

/**
 * File containing the Elasticsearch Section facet builder visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\FacetBuilderVisitor;

use eZ\Publish\Core\Search\Elasticsearch\Content\FacetBuilderVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * Visits the Section facet builder.
 */
class Section extends FacetBuilderVisitor
{
    /**
     * Check if visitor is applicable to current facet result.
     *
     * @param string $name
     *
     * @return bool
     */
    public function canMap($name)
    {
        return substr($name, 0, 9) === 'section__';
    }

    /**
     * Map Elasticsearch facet result back to facet objects.
     *
     * @param string $name
     * @param mixed $data
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\Facet
     */
    public function map($name, $data)
    {
        return new Facet\SectionFacet(
            [
                'name' => (string)substr($name, 9),
                'entries' => $this->mapData($data),
            ]
        );
    }

    /**
     * Check if visitor is applicable to current facet builder.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder $facetBuilder
     *
     * @return bool
     */
    public function canVisit(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof FacetBuilder\SectionFacetBuilder;
    }

    /**
     * Map facet builder to a proper Elasticsearch representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder $facetBuilder
     *
     * @return mixed
     */
    public function visit(FacetBuilder $facetBuilder)
    {
        return [
            "section__{$facetBuilder->name}" => [
                'terms' => [
                    'field' => 'section_id',
                    'min_doc_count' => $facetBuilder->minCount,
                    'size' => $facetBuilder->limit,
                ],
            ],
        ];
    }
}
