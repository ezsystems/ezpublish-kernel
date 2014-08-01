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

    /**
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document $document
     */
    public function index( Document $document )
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

    /**
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document[] $documents
     */
    public function bulkIndex( array $documents )
    {
        $payload = "";
        foreach ( $documents as $document )
        {
            $payload .= $this->getBulkIndexMetadataJson( $document ) . "\n";
            $payload .= $this->serializer->getJson( $document ) . "\n";
        }

        $result = $this->client->request(
            "POST",
            "/{$this->indexName}/_bulk",
            new Message(
                array(
                    "Content-Type" => "application/json",
                ),
                $payload
            )
        );

        if ( $result->headers["status"] !== 201 && $result->headers["status"] !== 200 )
        {
            throw new RuntimeException(
                "Wrong HTTP status received from Elasticsearch: " . $result->headers["status"]
            );
        }

        $this->flush();
    }

    protected function getBulkIndexMetadataJson( Document $document )
    {
        $metadataHash = array(
            "index" => array(
                "_type" => $document->type,
                "_id" => $document->id,
            ),
        );

        return json_encode( $metadataHash );
    }

    public function find( Query $query, $type )
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
            "/{$this->indexName}/{$type}/_search",
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

    public function purgeIndex( $type )
    {
        $result = $this->client->request( "DELETE", "/{$this->indexName}/{$type}/_query?q=id:*" );
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
        $this->client->request( "POST", "/_flush?full=false&force=false" );
    }
}
