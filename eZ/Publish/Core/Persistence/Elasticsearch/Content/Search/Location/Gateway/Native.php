<?php
/**
 * File containing the Location Search Native Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Location\Gateway;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Serializer;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Location\Gateway;
use eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway\HttpClient;
use eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway\Message;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor;
use RuntimeException;

/**
 *
 */
class Native extends Gateway
{
    /**
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway\HttpClient
     */
    protected $client;

    /**
     * Field value mapper
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Serializer
     */
    protected $mapper;

    /**
     * Query criterion visitor
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor
     */
    protected $criterionVisitor;

    /**
     * Query sort clause visitor
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor
     */
    protected $sortClauseVisitor;

    protected $indexName;

    public function __construct(
        HttpClient $client,
        Serializer $serializer,
        CriterionVisitor $criterionVisitor,
        SortClauseVisitor $sortClauseVisitor,
        $indexName = "ezpublish"
    )
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->criterionVisitor = $criterionVisitor;
        $this->sortClauseVisitor = $sortClauseVisitor;
        $this->indexName = $indexName;
    }

    public function indexDocument( Document $document )
    {
        $result = $this->client->request(
            "POST",
            "/{$this->indexName}/{$document->type}/{$document->id}",
            new Message(
                array(
                    "Content-Type" => "application/json",
                ),
                $json = $this->serializer->getJson( $document )
            )
        );

        if ( $result->headers["status"] !== 201 && $result->headers["status"] !== 200 )
        {
            throw new RuntimeException(
                "Wrong HTTP status received from Elasticsearch: " . $result->headers["status"]
            );
        }
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findLocations( LocationQuery $query )
    {
        $ast = array(
            "query" => $this->criterionVisitor->visit( $query->query ),
            "filter" => $this->criterionVisitor->visit( $query->filter ),
            "sort" => array_map(
                array( $this->sortClauseVisitor, "visit" ),
                $query->sortClauses
            ),
            "track_scores" => true,
        );

        if ( $query->offset !== null )
        {
            $ast["from"] = $query->offset;
        }

        // TODO: for some reason 1073741824 causes out of memory...
        if ( $query->limit !== null && $query->limit !== 1073741824 )
        {
            $ast["size"] = $query->limit;
        }

        $response = $this->client->request(
            "GET",
            "/{$this->indexName}/location/_search",
            new Message(
                array(
                    "Content-Type" => "application/json",
                ),
                $json = json_encode( $ast, JSON_PRETTY_PRINT )
            )
        );

        // TODO: error handling
        $data = json_decode( $response->body );

        return $data;
    }

    public function purgeIndex()
    {
        $result = $this->client->request( "DELETE", "/{$this->indexName}/location" );

        if ( $result->headers["status"] !== 200 )
        {
            //throw new RuntimeException(
            //    "Wrong HTTP status received from Elasticsearch: " . $result->headers["status"]
            //);
        }
    }

    public function flush()
    {
        $this->client->request( "POST", "/_flush?full=true&force=true" );
    }
}
