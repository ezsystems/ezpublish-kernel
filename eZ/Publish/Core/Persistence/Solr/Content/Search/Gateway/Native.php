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
    eZ\Publish\API\Repository\Values\Content\Query\Criterion;

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
     * Construct from HTTP client
     *
     * @param HttpClient $client
     * @return void
     */
    public function __construct( HttpClient $client )
    {
        $this->client = $client;
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

        var_dump( $result );
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
     * @param Document $document
     * @return string
     */
    protected function createUpdate( Document $document )
    {
        $xml = new \XmlWriter();
        $xml->openMemory();
        $xml->startElement( 'add' );
        $xml->startElement( 'doc' );

        foreach ( $document as $field )
        {
            $xml->startElement( 'field' );
            $xml->writeAttribute( 'name', $field->type );
            $xml->text( $field->value );
            $xml->endElement();
        }

        $xml->endElement();
        $xml->endElement();

        return $xml->outputMemory( true );
    }
}

