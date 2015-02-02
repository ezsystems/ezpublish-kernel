<?php
/**
 * File containing the Location Search Native Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\Location\Gateway;

use eZ\Publish\Core\Persistence\Solr\Content\Search\Location\Gateway;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FacetBuilderVisitor;
use eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway\HttpClient;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;

/**
 *
 */
class Native extends Gateway
{
    /**
     * HTTP client to communicate with Solr server
     *
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway\HttpClient
     */
    protected $client;

    /**
     * Query visitor
     *
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor
     */
    protected $criterionVisitor;

    /**
     * Sort clause visitor
     *
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor
     */
    protected $sortClauseVisitor;

    /**
     * Facet builder visitor
     *
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\FacetBuilderVisitor
     */
    protected $facetBuilderVisitor;

    /**
     * Content Handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * @var bool
     */
    protected $commit = true;

    /**
     * Construct from HTTP client
     *
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway\HttpClient $client
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor $criterionVisitor
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor $sortClauseVisitor
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\FacetBuilderVisitor $facetBuilderVisitor
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     */
    public function __construct(
        HttpClient $client,
        CriterionVisitor $criterionVisitor,
        SortClauseVisitor $sortClauseVisitor,
        FacetBuilderVisitor $facetBuilderVisitor,
        LocationHandler $locationHandler
    )
    {
        $this->client = $client;
        $this->criterionVisitor = $criterionVisitor;
        $this->sortClauseVisitor = $sortClauseVisitor;
        $this->facetBuilderVisitor = $facetBuilderVisitor;
        $this->locationHandler = $locationHandler;
    }

    /**
     * Finds Location objects for the given query.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findLocations( LocationQuery $query )
    {
        $parameters = array(
            "q" => 'document_type_id:"location" AND ' . $this->criterionVisitor->visit( $query->query ),
            "fq" => 'document_type_id:"location" AND ' . $this->criterionVisitor->visit( $query->filter ),
            "sort" => implode(
                ", ",
                array_map(
                    array( $this->sortClauseVisitor, "visit" ),
                    $query->sortClauses
                )
            ),
            "fl" => "*,score",
            "wt" => "json",
        );

        if ( $query->offset !== null )
        {
            $parameters["start"] = $query->offset;
        }

        if ( $query->limit !== null )
        {
            $parameters["rows"] = $query->limit;
        }

        // @todo: Extract method
        $response = $this->client->request(
            'GET',
            '/solr/select?' .
            http_build_query( $parameters ) .
            ( count( $query->facetBuilders ) ? '&facet=true&facet.sort=count&' : '' ) .
            implode(
                '&',
                array_map(
                    array( $this->facetBuilderVisitor, 'visit' ),
                    $query->facetBuilders
                )
            )
        );

        // @todo: Error handling?
        $data = json_decode( $response->body );

        if ( !isset( $data->response ) )
        {
            throw new \Exception( '->response not set: ' . var_export( array( $data, $parameters ), true ) );
        }

        // @todo: Extract method
        $result = new SearchResult(
            array(
                'time' => $data->responseHeader->QTime / 1000,
                'maxScore' => $data->response->maxScore,
                'totalCount' => $data->response->numFound,
            )
        );

        foreach ( $data->response->docs as $doc )
        {
            $searchHit = new SearchHit(
                array(
                    'score' => $doc->score,
                    'valueObject' => $this->locationHandler->load( substr( $doc->id, 8 ) )
                )
            );
            $result->searchHits[] = $searchHit;
        }

        if ( isset( $data->facet_counts ) )
        {
            foreach ( $data->facet_counts->facet_fields as $field => $facet )
            {
                $result->facets[] = $this->facetBuilderVisitor->map( $field, $facet );
            }
        }

        return $result;
    }
}
