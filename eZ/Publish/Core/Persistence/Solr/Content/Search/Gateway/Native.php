<?php
/**
 * File containing the Content Search Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway;

use eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler,
    eZ\Publish\SPI\Persistence\Content\Search\Field,
    eZ\Publish\SPI\Persistence\Content\Search\FieldType,
    eZ\Publish\API\Repository\Values\Content\Search\SearchResult,
    eZ\Publish\API\Repository\Values\Content\Search\SearchHit,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Query,
    eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor,
    eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor,
    eZ\Publish\Core\Persistence\Solr\Content\Search\FacetBuilderVisitor,
    eZ\Publish\Core\Persistence\Solr\Content\Search\FieldValueMapper;

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
     * Sort cluase visitor
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
     * Field valu mapper
     *
     * @var FieldValueMapper
     */
    protected $fieldValueMapper;

    /**
     * Content Handler
     *
     * @var ContentHandler
     */
    protected $contentHandler;

    /**
     * Simple mapping for our internal field types
     *
     * We implement this mapping, because those dynamic fields are common to
     * Solr configurations.
     *
     * @var array
     */
    protected $fieldNameMapping = array(
        "ez_integer"  => "i",
        "ez_string"   => "s",
        "ez_long"     => "l",
        "ez_text"     => "t",
        "ez_html"     => "h",
        "ez_boolean"  => "b",
        "ez_float"    => "f",
        "ez_double"   => "d",
        "ez_date"     => "dt",
        "ez_point"    => "p",
        "ez_currency" => "c",
    );

    /**
     * Construct from HTTP client
     *
     * @param HttpClient $client
     * @param CriterionVisitor $criterionVisitor
     * @param SortClauseVisitor $sortClauseVisitor
     * @param FacetBuilderVisitor $facetBuilderVisitor
     * @param FieldValueMapper $fieldValueMapper
     * @param ContentHandler $contentHandler
     * @return void
     */
    public function __construct( HttpClient $client, CriterionVisitor $criterionVisitor, SortClauseVisitor $sortClauseVisitor, FacetBuilderVisitor $facetBuilderVisitor, FieldValueMapper $fieldValueMapper, ContentHandler $contentHandler )
    {
        $this->client              = $client;
        $this->criterionVisitor    = $criterionVisitor;
        $this->sortClauseVisitor   = $sortClauseVisitor;
        $this->facetBuilderVisitor = $facetBuilderVisitor;
        $this->fieldValueMapper    = $fieldValueMapper;
        $this->contentHandler      = $contentHandler;
    }

     /**
     * finds content objects for the given query.
     *
     * @TODO define structs for the field filters
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array  $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findContent( Query $query, array $fieldFilters = array() )
    {
        // @TODO: Extract method
        $response = $this->client->request(
            'GET',
            '/solr/select?' .
                http_build_query( array(
                    'q'    => $this->criterionVisitor->visit( $query->criterion ),
                    'sort' => implode( ', ', array_map(
                        array( $this->sortClauseVisitor, 'visit' ),
                        $query->sortClauses
                    ) ),
                    'fl'   => '*,score',
                    'wt'   => 'json',
                ) ) .
                ( count( $query->facetBuilders ) ? '&facet=true&facet.sort=count&' : '' ) .
                implode( '&', array_map(
                    array( $this->facetBuilderVisitor, 'visit' ),
                    $query->facetBuilders
                ) )
        );
        // @TODO: Error handling?
        $data = json_decode( $response->body );

        // @TODO: Extract method
        $result = new SearchResult( array(
            'time'       => $data->responseHeader->QTime / 1000,
            'maxScore'   => $data->response->maxScore,
            'totalCount' => $data->response->numFound,
        ) );

        foreach ( $data->response->docs as $doc )
        {
            $searchHit = new SearchHit( array(
                'score'       => $doc->score,
                'valueObject' => $this->contentHandler->load( $doc->id, $doc->version_s )
            ) );
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
     * @param eZ\Publish\SPI\Persistence\Content\Search\Field[] $document
     * @return void
     */
    public function indexContent( array $document )
    {
        $update   = $this->createUpdate( $document );
        $result   = $this->client->request(
            'POST',
            '/solr/update?commit=true&wt=json',
            new Message(
                array(
                    'Content-Type: text/xml',
                ),
                $update
            )
        );

        // @TODO: Add error handling
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
            '/solr/update?commit=true&wt=json',
            new Message(
                array(
                    'Content-Type: text/xml',
                ),
                '<delete><query>*:*</query></delete>'
            )
        );
    }

    /**
     * Create document update XML
     *
     * @param array $document
     * @return string
     */
    protected function createUpdate( array $document )
    {
        $xml = new \XmlWriter();
        $xml->openMemory();
        $xml->startElement( 'add' );
        $xml->startElement( 'doc' );

        foreach ( $document as $field )
        {
            $values = (array) $this->fieldValueMapper->map( $field );
            foreach ( $values as $value )
            {
                $xml->startElement( 'field' );
                $xml->writeAttribute(
                    'name',
                    $this->mapFieldType( $field->name, $field->type )
                );
                $xml->text( $value );
                $xml->endElement();
            }
        }

        $xml->endElement();
        $xml->endElement();

        return $xml->outputMemory( true );
    }

    /**
     * Map field type
     *
     * For Solr indexing the follwing scheme will always be used for names:
     * {name}_{type}.
     *
     * Using dynamic fields this allows to define fields either depending on
     * types, or names.
     *
     * Only the field with the name ID remains untouched.
     *
     * @param string $name
     * @param FieldType $type
     * @return string
     */
    protected function mapFieldType( $name, FieldType $type )
    {
        if ( $name === "id" )
        {
            return $name;
        }

        $typeName = $type->type;
        if ( isset( $this->fieldNameMapping[$typeName] ) )
        {
            $typeName = $this->fieldNameMapping[$typeName];
        }

        return $name . '_' . $typeName;
    }
}

