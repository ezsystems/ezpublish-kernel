<?php
/**
 * File containing the Content Search Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Gateway;

use eZ\Publish\Core\Search\Solr\Content\Gateway;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Search\Solr\Content\CriterionVisitor;
use eZ\Publish\Core\Search\Solr\Content\SortClauseVisitor;
use eZ\Publish\Core\Search\Solr\Content\FacetBuilderVisitor;
use eZ\Publish\Core\Search\Solr\Content\FieldValueMapper;
use eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo;
use RuntimeException;
use XmlWriter;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\Document;
use eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointProvider;

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
     * @var \eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointProvider
     */
    protected $endpointProvider;

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
     * @param \eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointProvider $endpointProvider
     * @param CriterionVisitor $criterionVisitor
     * @param SortClauseVisitor $sortClauseVisitor
     * @param FacetBuilderVisitor $facetBuilderVisitor
     * @param FieldValueMapper $fieldValueMapper
     * @param FieldNameGenerator $nameGenerator
     */
    public function __construct(
        HttpClient $client,
        EndpointProvider $endpointProvider,
        CriterionVisitor $criterionVisitor,
        SortClauseVisitor $sortClauseVisitor,
        FacetBuilderVisitor $facetBuilderVisitor,
        FieldValueMapper $fieldValueMapper,
        FieldNameGenerator $nameGenerator
    )
    {
        $this->client              = $client;
        $this->endpointProvider = $endpointProvider;
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
        $coreFilter = $this->getCoreFilter( $fieldFilters );

        $parameters = array(
            "q" => $this->criterionVisitor->visit( $query->query ),
            "fq" => 'document_type_id:"content" AND ' . ( !empty( $coreFilter ) ? "({$coreFilter}) AND " : "" ) . $this->criterionVisitor->visit( $query->filter ),
            "sort" => implode(
                ", ",
                array_map(
                    array( $this->sortClauseVisitor, "visit" ),
                    $query->sortClauses
                )
            ),
            "start" => $query->offset,
            "rows" => $query->limit,
            "fl" => "*,score",
            "wt" => "json",
        );

        $endpoints = $this->endpointProvider->getSearchTargets( $fieldFilters );
        if ( !empty( $endpoints ) )
        {
            $parameters["shards"] = implode( ",", $endpoints );
        }

        // @todo: Extract method
        $response = $this->client->request(
            'GET',
            $this->endpointProvider->getEntryPoint(),
            '/select?' .
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
            $searchHit = new SearchHit(
                array(
                    'score'       => $doc->score,
                    'valueObject' => new SPIContentInfo(
                        array(
                            'id' => substr( $doc->id, 7 ),
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
     *
     *
     * @param array $languageSettings
     *
     * @return string
     */
    protected function getCoreFilter( array $languageSettings )
    {
        if ( empty( $languageSettings ) )
        {
            return "";
        }

        $filters = array();
        $languageCodes = $languageSettings["languages"];

        foreach ( $languageCodes as $languageCode )
        {
            $filters[] = "(" . $this->getCoreLanguageFilter( $languageCodes, $languageCode ) . ")";
        }

        if ( isset( $languageSettings["useAlwaysAvailable"] ) && $languageSettings["useAlwaysAvailable"] === true )
        {
            $filters[] = "meta_indexed_is_main_translation_and_always_available_b:true";
        }

        return implode( " OR ", $filters );
    }

    /**
     *
     *
     * @param array $languageCodes
     * @param string $selectedLanguageCode
     *
     * @return string
     */
    protected function getCoreLanguageFilter( array $languageCodes, $selectedLanguageCode )
    {
        $filters = array();
        $filters[] = 'meta_indexed_language_code_s:"' . $selectedLanguageCode . '"';

        foreach ( $languageCodes as $languageCode )
        {
            if ( $languageCode === $selectedLanguageCode )
            {
                break;
            }

            $filters[] = 'NOT language_code_ms:"' . $languageCode . '"';
        }

        return implode( " AND ", $filters );
    }

    /**
     * Indexes a block of documents, which in our case is a Content preceded by its Locations.
     * In Solr block is identifiable by '_root_' field which holds a parent document (Content) id.
     *
     * @param \eZ\Publish\SPI\Search\Document[] $documents
     *
     * @todo $documents should be generated more on demand then this and sent to Solr in chunks before final commit
     */
    public function bulkIndexDocuments( array $documents )
    {
        $map = array();

        foreach ( $documents as $documents2 )
        {
            foreach ( $documents2 as $document )
            {
                $map[$document->languageCode][] = $document;
            }
        }

        foreach ( $map as $languageCode => $translationDocuments )
        {
            $this->bulkIndexTranslationDocuments( $translationDocuments, $languageCode );
        }
    }

    protected function bulkIndexTranslationDocuments( array $documents, $languageCode )
    {
        $server = $this->endpointProvider->getIndexingTarget( $languageCode );

        $updates = $this->createUpdates( $documents );
        $result = $this->client->request(
            'POST',
            $server,
            '/update?' .
            ( $this->commit ? "softCommit=true&" : "" ) . 'wt=json',
            new Message(
                array(
                    'Content-Type' => 'text/xml',
                ),
                $updates
            )
        );

        if ( $result->headers["status"] !== 200 )
        {
            throw new RuntimeException(
                "Wrong HTTP status received from Solr: " . $result->headers["status"] . var_export( array( $result, $updates ), true )
            );
        }
    }

    /**
     * Deletes a block of documents, which in our case is a Content preceded by its Locations.
     * In Solr block is identifiable by '_root_' field which holds a parent document (Content) id.
     *
     * @todo to be removed
     *
     * @param string $blockId
     */
    public function deleteBlock( $blockId )
    {
        $endpoints = $this->endpointProvider->getAllEndpoints();

        foreach ( $endpoints as $endpoint )
        {
            $this->client->request(
                'POST',
                $endpoint,
                '/update?' .
                ( $this->commit ? "softCommit=true&" : "" ) . 'wt=json',
                new Message(
                    array(
                        'Content-Type' => 'text/xml',
                    ),
                    "<delete><query>_root_:" . $blockId . "</query></delete>"
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
        $endpoints = $this->endpointProvider->getAllEndpoints();

        foreach ( $endpoints as $endpoint )
        {
            $this->purgeEndpoint( $endpoint );
        }
    }

    /**
     * @todo error handling
     *
     * @param $endpoint
     */
    protected function purgeEndpoint( $endpoint )
    {
        $this->client->request(
            'POST',
            $endpoint,
            '/update?' .
            ( $this->commit ? "softCommit=true&" : "" ) . 'wt=json',
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
     * @param \eZ\Publish\SPI\Search\Document[] $documents
     *
     * @return string
     */
    protected function createUpdates( array $documents )
    {
        $xmlWriter = new XmlWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startElement( 'add' );

        foreach ( $documents as $document )
        {
            $this->writeDocument( $xmlWriter, $document );
        }

        $xmlWriter->endElement();

        return $xmlWriter->outputMemory( true );
    }

    protected function writeDocument( XmlWriter $xmlWriter, Document $document )
    {
        $xmlWriter->startElement( 'doc' );

        foreach ( $document->fields as $field )
        {
            $this->writeField( $xmlWriter, $field );
        }

        foreach ( $document->documents as $document )
        {
            $this->writeDocument( $xmlWriter, $document );
        }

        $xmlWriter->endElement();
    }

    protected function writeField( XmlWriter $xmlWriter, Field $field )
    {
        foreach ( (array)$this->fieldValueMapper->map( $field ) as $value )
        {
            $xmlWriter->startElement( 'field' );
            $xmlWriter->writeAttribute(
                'name',
                $this->nameGenerator->getTypedName( $field->name, $field->type )
            );
            $xmlWriter->text( $value );
            $xmlWriter->endElement();
        }
    }
}
