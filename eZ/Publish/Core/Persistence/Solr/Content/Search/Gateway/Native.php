<?php
/**
 * File containing the Content Search Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway;

use eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldNameGenerator;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FacetBuilderVisitor;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldValueMapper;
use eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo;
use eZ\Publish\SPI\Search\Document;
use eZ\Publish\SPI\Search\ChildDocuments;
use RuntimeException;
use XmlWriter;

/**
 * The Content Search Gateway provides the implementation for one database to
 * retrieve the desired content objects.
 */
class Native extends Gateway
{
    /**
     * HTTP client to communicate with Solr server
     *
     * @var HttpClient
     */
    protected $client;

    /**
     * Query visitor
     *
     * @var CriterionVisitor
     */
    protected $criterionVisitor;

    /**
     * Sort clause visitor
     *
     * @var SortClauseVisitor
     */
    protected $sortClauseVisitor;

    /**
     * Facet builder visitor
     *
     * @var FacetBuilderVisitor
     */
    protected $facetBuilderVisitor;

    /**
     * Field value mapper
     *
     * @var FieldValueMapper
     */
    protected $fieldValueMapper;

    /**
     * Field name generator
     *
     * @var FieldNameGenerator
     */
    protected $nameGenerator;

    /**
     * @var bool
     */
    protected $commit = true;

    /**
     * Construct from HTTP client
     *
     * @param HttpClient $client
     * @param CriterionVisitor $criterionVisitor
     * @param SortClauseVisitor $sortClauseVisitor
     * @param FacetBuilderVisitor $facetBuilderVisitor
     * @param FieldValueMapper $fieldValueMapper
     * @param FieldNameGenerator $nameGenerator
     */
    public function __construct( HttpClient $client, CriterionVisitor $criterionVisitor, SortClauseVisitor $sortClauseVisitor, FacetBuilderVisitor $facetBuilderVisitor, FieldValueMapper $fieldValueMapper, FieldNameGenerator $nameGenerator )
    {
        $this->client              = $client;
        $this->criterionVisitor    = $criterionVisitor;
        $this->sortClauseVisitor   = $sortClauseVisitor;
        $this->facetBuilderVisitor = $facetBuilderVisitor;
        $this->fieldValueMapper    = $fieldValueMapper;
        $this->nameGenerator       = $nameGenerator;
    }

    /**
     * Finds content objects for the given query.
     *
     * @todo define structs for the field filters
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findContent( Query $query, array $fieldFilters = array() )
    {
        $parameters = array(
            "q" => $this->criterionVisitor->visit( $query->query ),
            "fq" => $this->criterionVisitor->visit( $query->filter ),
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
                'time'       => $data->responseHeader->QTime / 1000,
                'maxScore'   => $data->response->maxScore,
                'totalCount' => $data->response->numFound,
            )
        );

        foreach ( $data->response->docs as $doc )
        {
            if ( !isset( $doc->name_s ) )
            {
                throw new \Exception( '->name_s not set: ' . var_export( array( $doc, $parameters ), true ) );
            }

            $searchHit = new SearchHit(
                array(
                    'score'       => $doc->score,
                    'valueObject' => new SPIContentInfo(
                        array(
                            'id' => $doc->id,
                            'name' => $doc->name_s,
                            'contentTypeId' => $doc->type_id,
                            'sectionId' => $doc->section_id,
                            'currentVersionNo' => $doc->version_id,
                            'isPublished' => $doc->status_id === SPIContentInfo::STATUS_PUBLISHED,
                            'ownerId' => $doc->owner_id,
                            'modificationDate' => $doc->modified_dt,
                            'publicationDate' => $doc->published_dt,
                            'alwaysAvailable' => $doc->always_available_b,
                            'remoteId' => $doc->remote_id_id,
                            'mainLanguageCode' => $doc->main_language_code_s,
                            'mainLocationId' => ( isset( $doc->main_location_id ) ? $doc->main_location_id : null )
                        )
                    )
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

    /**
     * Indexes a content object
     *
     * @param \eZ\Publish\SPI\Search\Document[] $documents
     * @todo $documents should be generated more on demand then this and sent to Solr in chunks before final commit
     *
     * @return void
     */
    public function bulkIndexContent( array $documents )
    {
        $updates   = $this->createUpdates( $documents );
        $result   = $this->client->request(
            'POST',
            '/solr/update?' . ( $this->commit ? "softCommit=true&" : "" ) . 'wt=json',
            new Message(
                array(
                    'Content-Type' => 'text/xml',
                ),
                $updates
            )
        );

        if ( $result->headers["status"] !== 200 )
        {
            var_dump( $result );
            throw new RuntimeException( "Wrong HTTP status received from Solr: " . $result->headers["status"] );
        }
    }

    /**
     * Deletes a content object from the index
     *
     * @param int content id
     * @param int|null version id
     *
     * @return void
     */
    public function deleteContent( $contentId, $versionId = null )
    {
        $this->client->request(
            'POST',
            '/solr/update?' . ( $this->commit ? "softCommit=true&" : "" ) . 'wt=json',
            new Message(
                array(
                    'Content-Type' => 'text/xml',
                ),
                "<delete><query>id:" . (int)$contentId . ( $versionId !== null ? " AND version_id:" . (int)$versionId : "" ) . "</query></delete>"
            )
        );
    }

    /**
     * Deletes a location from the index
     *
     * @param mixed $locationId
     */
    public function deleteLocation( $locationId )
    {
        $response = $this->client->request(
            'GET',
            '/solr/select?' .
            http_build_query(
                array(
                    "q" => '{!parent which="doc_type_id:content"}path_id:*/$locationId/*',
                    "fl" => "*,[child parentFilter=doc_type_id:content childFilter=doc_type_id:location limit=100]",
                    "wt" => "json",
                )
            )
        );
        // @todo: Error handling?
        $data = json_decode( $response->body );

        $locationParent = array( $locationId );
        $contentToDelete = $contentToUpdate = array();
        foreach ( $data->response->docs as $doc )
        {
            // Check that this document only had one location in which case it can be removed.
            // @todo When orphaned objects will be possible, we will have to update those doc instead of removing.
            if ( count( $doc->_childDocuments_ ) === 1 )
            {
                $contentToDelete[] = $doc->id;
            }
            else
            {
                $contentToUpdate[] = $doc;
            }
        }

        if ( !empty( $contentToDelete ) )
        {
            $this->client->request(
                "POST",
                "/solr/update?" . ( $this->commit ? "softCommit=true&" : "" ) . "wt=json",
                new Message(
                    array(
                        "Content-Type" => "text/xml",
                    ),
                    "<delete><query>id:(" . implode( " ", $contentToDelete ) . ")</query></delete>"
                )
            );
        }

        if ( !empty( $contentToUpdate ) )
        {
            $jsonString = "";
            foreach ( $contentToUpdate as $doc )
            {
                // @todo How are these looking now? They are not even here until we as to expand or something
                // Removing location references in location_parent_mid, location_mid and path_mid
                // main_* fields are not modified since removing main node is not permitted.
                foreach ( $doc->_childDocuments_ as $key => $location )
                {
                    if (
                        $location->location_id == $locationId ||
                        $location->partent_id == $locationId ||
                        strpos( $location->path_id, "/$locationId/" ) === false
                    )
                        unset( $doc->_childDocuments_[$key] );
                }


                if ( !empty( $jsonString ) )
                    $jsonString .= ",";

                $jsonString .= '"add": { "doc": ' . json_encode( $doc ) . "}";
            }

            $this->client->request(
                "POST",
                "/solr/update/json?" . ( $this->commit ? "softCommit=true&" : "" ) . "wt=json",
                new Message(
                    array(
                        "Content-Type: application/json",
                    ),
                    "{ $jsonString }"
                )
            );
        }
    }

    /**
     * Purges all contents from the index
     *
     * @return void
     */
    public function purgeIndex()
    {
        $this->client->request(
            'POST',
            '/solr/update?' . ( $this->commit ? "softCommit=true&" : "" ) . 'wt=json',
            new Message(
                array(
                    'Content-Type' => 'text/xml',
                ),
                '<delete><query>*:*</query></delete>'
            )
        );
    }

    /**
     * @param bool $commit
     */
    public function setCommit( $commit )
    {
        $this->commit = !!$commit;
    }

    /**
     * Create document(s) update XML
     *
     * @param array $documents
     *
     * @return string
     */
    protected function createUpdates( array $documents )
    {
        $xml = new XmlWriter();
        $xml->openMemory();
        $xml->startElement( 'add' );

        $this->writeDocuments( $documents, $xml );

        $xml->endElement();
        return $xml->outputMemory( true );
    }
    /**
     * Write documents update XML
     *
     * @param array $documents
     * @param XmlWriter $xml
     */
    private function writeDocuments( array $documents, XmlWriter $xml )
    {
        foreach ( $documents as $document )
        {
            $this->writeDocument( $document, $xml );
        }
    }

    /**
     * Write document update XML
     *
     * @param Document $document
     * @param XmlWriter $xml
     */
    private function writeDocument( Document $document, XmlWriter $xml )
    {
        $xml->startElement( 'doc' );
        foreach ( $document->members as $member )
        {
            if ( $member instanceof ChildDocuments )
            {
                $this->writeDocuments( $member->members, $xml );
                continue;
            }

            foreach ( (array)$this->fieldValueMapper->map( $member ) as $value )
            {
                $xml->startElement( 'field' );
                $xml->writeAttribute(
                    'name',
                    $this->nameGenerator->getTypedName( $member->name, $member->type )
                );
                $xml->text( $value );
                $xml->endElement();
            }
        }
        $xml->endElement();
    }
}
