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
    eZ\Publish\SPI\Persistence\Content\Search\DocumentField,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
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
     * Field valu mapper
     *
     * @var FieldValueMapper
     */
    protected $fieldValueMapper;

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
     * @return void
     */
    public function __construct( HttpClient $client, FieldValueMapper $fieldValueMapper )
    {
        $this->client           = $client;
        $this->fieldValueMapper = $fieldValueMapper;
    }

    /**
     * Indexes a content object
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @return void
     */
    public function indexContent( Content $content )
    {
        $document = $this->mapContent( $content );
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
     * Map content to document.
     *
     * A document is an array of fields
     *
     * @param Content $content
     * @return array
     */
    protected function mapContent( Content $content )
    {
        return array(
            new DocumentField\StringField( array(
                'name'  => 'id',
                'value' => $content->contentInfo->id,
            ) ),
            new DocumentField\StringField( array(
                'name'  => 'name',
                'value' => $content->contentInfo->name,
            ) ),
            new DocumentField\DateField( array(
                'name'  => 'modified',
                'value' => $content->contentInfo->modificationDate,
            ) ),
            new DocumentField\DateField( array(
                'name'  => 'published',
                'value' => $content->contentInfo->publicationDate,
            ) ),
            new DocumentField\StringField( array(
                'name'  => 'location',
                'value' => array_map(
                    function ( $location )
                    {
                        return $location->pathString;
                    },
                    $content->locations
                ),
            ) ),
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
     * @param string $type
     * @return string
     */
    protected function mapFieldType( $name, $type )
    {
        if ( $name === "id" )
        {
            return $name;
        }

        if ( isset( $this->fieldNameMapping[$type] ) )
        {
            $type = $this->fieldNameMapping[$type];
        }

        return $name . '_' . $type;
    }
}

