<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\Search\Solr\Content\FacetBuilderVisitor;

/**
 * Abstract implementation of Search Extractor, which extracts search result
 * from the data returned by Solr backend.
 */
abstract class ResultExtractor
{
    /**
     * Facet builder visitor
     *
     * @var \eZ\Publish\Core\Search\Elasticsearch\Content\FacetBuilderVisitor
     */
    protected $facetBuilderVisitor;

    public function __construct( FacetBuilderVisitor $facetBuilderVisitor )
    {
        $this->facetBuilderVisitor = $facetBuilderVisitor;
    }

    /**
     * Extracts search result from $data returned by Solr backend.
     *
     * @param mixed $data
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function extract( $data )
    {
        $result = new SearchResult(
            array(
                "time" => $data->responseHeader->QTime / 1000,
                "maxScore" => $data->response->maxScore,
                "totalCount" => $data->response->numFound,
            )
        );

        if ( isset( $data->facet_counts ) )
        {
            foreach ( $data->facet_counts->facet_fields as $field => $facet )
            {
                $result->facets[] = $this->facetBuilderVisitor->map(
                    $field,
                    $facet
                );
            }
        }

        foreach ( $data->response->docs as $doc )
        {
            $searchHit = new SearchHit(
                array(
                    "score" => $doc->score,
                    "index" => $this->getIndexIdentifier( $doc ),
                    "contentTranslation" => $this->getMatchedLanguageCode( $doc ),
                    "valueObject" => $this->extractHit( $doc ),
                )
            );
            $result->searchHits[] = $searchHit;
        }

        return $result;
    }

    /**
     * Returns language code of the Content's translation of the matched document.
     *
     * @param $hit
     */
    protected function getMatchedLanguageCode( $hit )
    {
        return $hit->meta_indexed_language_code_s;
    }

    /**
     * Returns the identifier of the logical index (shard) of the matched document.
     *
     * @param mixed $hit
     *
     * @return string
     */
    protected function getIndexIdentifier( $hit )
    {
        return $hit->{"[shard]"};
    }

    /**
     * Extracts value object from $hit returned by Solr backend.
     *
     * Needs to be implemented by the concrete ResultExtractor.
     *
     * @param mixed $hit
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    abstract public function extractHit( $hit );
}
