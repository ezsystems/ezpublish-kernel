<?php
/**
 * File containing the Content Search Native Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Serializer;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway;
use eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway\HttpClient;
use eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway\Message;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use RuntimeException;

/**
 * The Content Search Gateway provides the implementation for one database to
 * retrieve the desired content objects.
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

    /**
     * Content Handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    protected $indexName;

    public function __construct(
        HttpClient $client,
        Serializer $serializer,
        CriterionVisitor $criterionVisitor,
        SortClauseVisitor $sortClauseVisitor,
        ContentHandler $contentHandler,
        $indexName = "ezpublish"
    )
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->criterionVisitor = $criterionVisitor;
        $this->sortClauseVisitor = $sortClauseVisitor;
        $this->contentHandler = $contentHandler;
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

        $this->flush();

        if ( $result->headers["status"] !== 201 && $result->headers["status"] !== 200 )
        {
            throw new RuntimeException(
                "Wrong HTTP status received from Elasticsearch: " . $result->headers["status"]
            );
        }
    }

    public function findContent( Query $query )
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
            "/{$this->indexName}/content/_search",
            new Message(
                array(
                    "Content-Type" => "application/json",
                ),
                $json = json_encode( $ast, JSON_PRETTY_PRINT )
            )
        );

        // TODO: error handling
        $data = json_decode( $response->body );

        return $this->extractResult( $data );
    }

    protected function extractResult( $data )
    {
        $result = new SearchResult(
            array(
                "time" => $data->took,
                "maxScore" => $data->hits->max_score,
                "totalCount" => $data->hits->total,
            )
        );

        foreach ( $data->hits->hits as $hit )
        {
            $searchHit = new SearchHit(
                array(
                    "score" => $hit->_score,
                    "valueObject" => $this->contentHandler->load(
                        $hit->_id,
                        $hit->_source->version_id
                    )
                )
            );
            $result->searchHits[] = $searchHit;
        }

        return $result;
    }

    public function purgeIndex()
    {
        $result = $this->client->request( "DELETE", "/{$this->indexName}/_query?q=id:*" );
        $this->flush();

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
