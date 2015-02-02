<?php
/**
 * File containing the Elasticsearch Native Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Serializer;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FacetBuilderVisitor;
use ArrayObject;
use RuntimeException;

/**
 * The Native Gateway provides the implementation to retrieve the desired
 * documents from Elasticsearch index storage.
 */
class Native extends Gateway
{
    /**
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway\HttpClient
     */
    protected $client;

    /**
     * Document serializer
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Serializer
     */
    protected $serializer;

    /**
     * Query criterion visitor dispatcher
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher
     */
    protected $criterionVisitorDispatcher;

    /**
     * Query sort clause visitor
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor
     */
    protected $sortClauseVisitor;

    /**
     * Query facet builder visitor
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FacetBuilderVisitor
     */
    protected $facetBuilderVisitor;

    protected $indexName;

    /**
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway\HttpClient $client
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Serializer $serializer
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher $criterionVisitorDispatcher
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor $sortClauseVisitor
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FacetBuilderVisitor $facetBuilderVisitor
     * @param string $indexName
     */
    public function __construct(
        HttpClient $client,
        Serializer $serializer,
        CriterionVisitorDispatcher $criterionVisitorDispatcher,
        SortClauseVisitor $sortClauseVisitor,
        FacetBuilderVisitor $facetBuilderVisitor,
        $indexName = "ezpublish"
    )
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->criterionVisitorDispatcher = $criterionVisitorDispatcher;
        $this->sortClauseVisitor = $sortClauseVisitor;
        $this->facetBuilderVisitor = $facetBuilderVisitor;
        $this->indexName = $indexName;
    }

    /**
     * Indexes a given $document.
     *
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
                $json = $this->serializer->getIndexDocument( $document )
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
     * Performs bulk index of a given array of documents.
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document[] $documents
     */
    public function bulkIndex( array $documents )
    {
        if ( empty( $documents ) )
        {
            return;
        }

        $payload = "";
        foreach ( $documents as $document )
        {
            $payload .= $this->serializer->getIndexMetadata( $document ) . "\n";
            $payload .= $this->serializer->getIndexDocument( $document ) . "\n";
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

    /**
     * Finds and returns documents of a given $type for a given $query object.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param string $type
     * @param array $fieldFilters
     *
     * @return mixed
     */
    public function find( Query $query, $type, array $fieldFilters = array() )
    {
        $aggregationList = array_map(
            array( $this->facetBuilderVisitor, 'visit' ),
            $query->facetBuilders
        );

        $aggregations = array();
        foreach ( $aggregationList as $aggregation )
        {
            $aggregations[key( $aggregation )] = reset( $aggregation );
        }

        $ast = array(
            "query" => array(
                "filtered" => array(
                    "query" => array(
                        $this->criterionVisitorDispatcher->dispatch(
                            $query->query,
                            CriterionVisitorDispatcher::CONTEXT_QUERY,
                            $fieldFilters
                        ),
                    ),
                    "filter" => array(
                        $this->criterionVisitorDispatcher->dispatch(
                            $query->filter,
                            CriterionVisitorDispatcher::CONTEXT_FILTER,
                            $fieldFilters
                        ),
                    ),
                ),
            ),
            // Filters are added through 'filtered' query, because aggregations operate in query scope
            //"filter" => ...
            "aggregations" => empty( $aggregations ) ? new ArrayObject : $aggregations,
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
        if ( $query->limit == 1073741824 )
        {
            $ast["size"] = 1000;
        }

        $response = $this->findRaw( json_encode( $ast ), $type );

        // TODO: error handling
        $data = json_decode( $response->body );

        return $data;
    }

    /**
     * Finds and returns documents of a given $type for a given $query string.
     *
     * @param string $query
     * @param string $type
     *
     * @return \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway\Message
     */
    public function findRaw( $query, $type )
    {
        $response = $this->client->request(
            "GET",
            "/{$this->indexName}/{$type}/_search",
            new Message(
                array(
                    "Content-Type" => "application/json",
                ),
                $query
            )
        );

        return $response;
    }

    /**
     * Deletes all documents of a given $type from the index.
     *
     * @param string $type
     */
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

    /**
     * Deletes a single document of the given $type by given document $id.
     *
     * @param int|string $id
     * @param string $type
     */
    public function delete( $id, $type )
    {
        $result = $this->client->request( "DELETE", "/{$this->indexName}/{$type}/{$id}" );
        $this->flush();
    }

    /**
     * Deletes a document(s) of the given $type by given $query string.
     *
     * @param string $query
     * @param string $type
     */
    public function deleteByQuery( $query, $type )
    {
        $result = $this->client->request(
            "DELETE",
            "/{$this->indexName}/{$type}/_query",
            new Message(
                array(
                    "Content-Type" => "application/json",
                ),
                $query
            )
        );
        $this->flush();
    }

    /**
     * Flushes data from memory to the index storage.
     */
    public function flush()
    {
        $this->client->request( "POST", "/_flush?full=false&force=false" );
    }
}
