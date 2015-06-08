<?php
/**
 * File containing the Content Search Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Gateway;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\Search\Solr\Content\Gateway;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Search\Solr\Content\CriterionVisitor;
use eZ\Publish\Core\Search\Solr\Content\SortClauseVisitor;
use eZ\Publish\Core\Search\Solr\Content\FacetBuilderVisitor;
use eZ\Publish\Core\Search\Solr\Content\FieldValueMapper;
use RuntimeException;
use XmlWriter;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\Document;

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
     * @var \eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointResolver
     */
    protected $endpointResolver;

    /**
     * Endpoint registry service
     *
     * @var \eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointRegistry
     */
    protected $endpointRegistry;

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
     * @param \eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointResolver $endpointResolver
     * @param \eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointRegistry $endpointRegistry
     * @param CriterionVisitor $criterionVisitor
     * @param SortClauseVisitor $sortClauseVisitor
     * @param FacetBuilderVisitor $facetBuilderVisitor
     * @param FieldValueMapper $fieldValueMapper
     * @param FieldNameGenerator $nameGenerator
     */
    public function __construct(
        HttpClient $client,
        EndpointResolver $endpointResolver,
        EndpointRegistry $endpointRegistry,
        CriterionVisitor $criterionVisitor,
        SortClauseVisitor $sortClauseVisitor,
        FacetBuilderVisitor $facetBuilderVisitor,
        FieldValueMapper $fieldValueMapper,
        FieldNameGenerator $nameGenerator
    )
    {
        $this->client              = $client;
        $this->endpointResolver = $endpointResolver;
        $this->endpointRegistry = $endpointRegistry;
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
     * @return mixed
     */
    public function find( Query $query, array $fieldFilters = array() )
    {
        $documentType = "content";
        if ( $query instanceof LocationQuery )
        {
            $documentType = "location";
        }

        $parameters = array(
            "q" => $this->criterionVisitor->visit( $query->query ),
            "fq" => "document_type_id:{$documentType} AND (" . $this->criterionVisitor->visit( $query->filter ) . ")",
            "sort" => $this->getSortClauses( $query->sortClauses ),
            "start" => $query->offset,
            "rows" => $query->limit,
            "fl" => "*,score",
            "wt" => "json",
        );

        $coreFilter = $this->getCoreFilter( $fieldFilters );
        if ( !empty( $coreFilter ) )
        {
            $parameters["fq"] = "({$coreFilter}) AND (" . $parameters["fq"] . ")";
        }

        $searchTargets = $this->getSearchTargets( $fieldFilters );
        if ( !empty( $searchTargets ) )
        {
            $parameters["shards"] = $searchTargets;
        }

        $queryString = http_build_query( $parameters );

        $facets = $this->getFacets( $query->facetBuilders );
        if ( !empty( $facets ) )
        {
            $queryString .= "&facet=true&facet.sort=count&{$facets}";
        }

        $response = $this->client->request(
            'GET',
            $this->endpointRegistry->getEndpoint(
                $this->endpointResolver->getEntryEndpoint()
            ),
            "/select?{$queryString}"
        );

        // @todo: Error handling?
        $result = json_decode( $response->body );

        if ( !isset( $result->response ) )
        {
            throw new \Exception(
                '->response not set: ' . var_export( array( $result, $parameters ), true )
            );
        }

        return $result;
    }

    /**
     * Converts an array of sort clause objects to a proper Solr representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     *
     * @return string
     */
    protected function getSortClauses( array $sortClauses )
    {
        return implode(
            ", ",
            array_map(
                array( $this->sortClauseVisitor, "visit" ),
                $sortClauses
            )
        );
    }

    /**
     * Converts an array of facet builder objects to a proper Solr representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @return string
     */
    protected function getFacets( array $facetBuilders )
    {
        return implode(
            '&',
            array_map(
                array( $this->facetBuilderVisitor, 'visit' ),
                $facetBuilders
            )
        );
    }

    /**
     * Returns search targets for given language settings
     *
     * @param array $languageSettings
     *
     * @return string
     */
    protected function getSearchTargets( $languageSettings )
    {
        $shards = array();
        $endpoints = $this->endpointResolver->getSearchTargets( $languageSettings );

        if ( !empty( $endpoints ) )
        {
            foreach ( $endpoints as $endpoint )
            {
                $shards[] = $this->endpointRegistry->getEndpoint( $endpoint )->getIdentifier();
            }
        }

        return implode( ",", $shards );
    }

    /**
     * Returns a filtering condition for the given language settings.
     *
     * The condition ensures the same Content will be matched only once across all
     * targeted translation endpoints.
     *
     * @param array $languageSettings
     *
     * @return string
     */
    protected function getCoreFilter( array $languageSettings )
    {
        $filters = array();

        if ( !empty( $languageSettings["languages"] ) )
        {
            foreach ( $languageSettings["languages"] as $languageCode )
            {
                $filters[] = "(" . $this->getCoreLanguageFilter( $languageSettings["languages"], $languageCode ) . ")";
            }
        }

        if ( isset( $languageSettings["useAlwaysAvailable"] ) && $languageSettings["useAlwaysAvailable"] === true )
        {
            $filters[] = "meta_indexed_is_main_translation_and_always_available_b:true";
        }
        else if ( empty( $languageSettings["languages"] ) )
        {
            $filters[] = "meta_indexed_is_main_translation_b:true";
        }

        return implode( " OR ", $filters );
    }

    /**
     * Returns a filtering condition for the given list of language codes and
     * a selected language code among them.
     *
     * Note that the list of language codes is assumed to be prioritized, that is sorted by
     * priority, descending.
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
     * Indexes an array of documents.
     *
     * Documents are given as an array of the array of documents. The array of documents
     * holds documents for all translations of the particular entity.
     *
     * @param \eZ\Publish\SPI\Search\Document[][] $documents
     *
     * @todo $documents should be generated more on demand then this and sent to Solr in chunks before final commit
     */
    public function bulkIndexDocuments( array $documents )
    {
        $map = array();

        foreach ( $documents as $translationDocuments )
        {
            foreach ( $translationDocuments as $document )
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
        $updates = $this->createUpdates( $documents );
        $result = $this->client->request(
            'POST',
            $this->endpointRegistry->getEndpoint(
                $this->endpointResolver->getIndexingTarget( $languageCode )
            ),
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
     * Deletes documents by the given $query.
     *
     * @param string $query
     */
    public function deleteByQuery( $query )
    {
        $endpoints = $this->endpointResolver->getEndpoints();

        foreach ( $endpoints as $endpointName )
        {
            $this->client->request(
                'POST',
                $this->endpointRegistry->getEndpoint( $endpointName ),
                '/update?' .
                ( $this->commit ? "softCommit=true&" : "" ) . 'wt=json',
                new Message(
                    array(
                        'Content-Type' => 'text/xml',
                    ),
                    "<delete><query>{$query}</query></delete>"
                )
            );
        }
    }

    /**
     * @todo implement purging for document type
     *
     * Purges all contents from the index
     *
     * @return void
     */
    public function purgeIndex()
    {
        $endpoints = $this->endpointResolver->getEndpoints();

        foreach ( $endpoints as $endpointName )
        {
            $this->purgeEndpoint(
                $this->endpointRegistry->getEndpoint( $endpointName )
            );
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
