<?php
/**
 * File containing the eZ\Publish\Core\FieldType\RichText\Converter\Embed class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RichText\Converter;

use eZ\Publish\Core\FieldType\RichText\Converter;
use eZ\Publish\Core\FieldType\RichText\EmbedRendererInterface;
use Psr\Log\LoggerInterface;
use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * RichText Embed converter injects rendered embed payloads into embed elements.
 */
class Embed implements Converter
{
    /**
     * @var \eZ\Publish\Core\FieldType\RichText\EmbedRendererInterface
     */
    protected $embedRenderer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct( EmbedRendererInterface $embedRenderer, LoggerInterface $logger )
    {
        $this->embedRenderer = $embedRenderer;
        $this->logger = $logger;
    }

    /**
     * Processes single embed element type (ezembed or ezembedinline)
     *
     * @param \DOMDocument $document
     * @param $tagName string name of the tag to extract
     */
    protected function processTag( DOMDocument $document, $tagName )
    {
        /** @var $embed \DOMElement */
        foreach ( $document->getElementsByTagName( $tagName ) as $embed )
        {
            if ( !$viewType = $embed->getAttribute( "view" ) )
            {
                // Mapping default view names
                $map = array(
                    "ezembed" => "embed",
                    "ezembedinline" => "embed-inline"
                );

                $viewType = $map[$tagName];
            }

            $embedContent = null;
            $parameters = array(
                "embedParams" => $this->extractConfiguration( $embed )
            );
            $resourceReference = $embed->getAttribute( "xlink:href" );

            if ( empty( $resourceReference ) )
            {
                if ( isset( $this->logger ) )
                {
                    $this->logger->error( "Could not embed resource: empty 'xlink:href' attribute" );
                }
            }
            else if ( 0 === preg_match( "~^(ezcontent|ezlocation)://(.*)$~", $resourceReference, $matches ) )
            {
                if ( isset( $this->logger ) )
                {
                    $this->logger->error(
                        "Could not embed resource: unhandled resource reference '{$resourceReference}'"
                    );
                }
            }
            else if ( $matches[1] === "ezcontent" )
            {
                $embedContent = $this->embedRenderer->renderContent( $matches[2], $viewType, $parameters );
            }
            else if ( $matches[1] === "ezlocation" )
            {
                $embedContent = $this->embedRenderer->renderLocation( $matches[2], $viewType, $parameters );
            }

            if ( isset( $embedContent ) )
            {
                $payload = $document->createElement( "ezpayload" );
                $payload->appendChild( $document->createCDATASection( $embedContent ) );
                $embed->appendChild( $payload );
            }
        }
    }

    /**
     * Extracts configuration hash from embed element
     *
     * @param \DOMElement $embed
     *
     * @return array
     */
    protected function extractConfiguration( DOMElement $embed )
    {
        $hash = array();
        $configElements = $embed->getElementsByTagName( "ezconfig" );

        if ( $configElements->length )
        {
            $hash = $this->extractHash( $configElements->item( 0 ) );
        }

        return $hash;
    }

    /**
     * Recursively extracts data from XML hash structure
     *
     * @param \DOMNode $configHash
     *
     * @return array
     */
    protected function extractHash( DOMNode $configHash )
    {
        $hash = array();

        foreach ( $configHash->childNodes as $node )
        {
            /** @var \DOMText|\DOMElement $node */
            if ( $node->nodeType === XML_ELEMENT_NODE )
            {
                $hash[$node->getAttribute( "key" )] = $this->extractHash( $node );
            }
            else if ( $node->nodeType === XML_TEXT_NODE && !$node->isWhitespaceInElementContent() )
            {
                return $node->wholeText;
            }
        }

        return $hash;
    }

    /**
     * Injects rendered payloads into embed elements
     *
     * @param \DOMDocument $document
     *
     * @return null
     */
    public function convert( DOMDocument $document )
    {
        $this->processTag( $document, 'ezembed' );
        $this->processTag( $document, 'ezembedinline' );

        return $document;
    }
}
