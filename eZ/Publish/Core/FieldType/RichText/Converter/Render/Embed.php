<?php
/**
 * File containing the eZ\Publish\Core\FieldType\RichText\Converter\Render\Embed class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RichText\Converter\Render;

use eZ\Publish\Core\FieldType\RichText\RendererInterface;
use eZ\Publish\Core\FieldType\RichText\Converter;
use eZ\Publish\Core\FieldType\RichText\Converter\Render;
use Psr\Log\LoggerInterface;
use DOMDocument;

/**
 * RichText Template converter injects rendered template payloads into template elements.
 */
class Embed extends Render implements Converter
{
    /**
     * @var null|\Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Maps embed tag names to their default views
     *
     * @var array
     */
    protected $tagDefaultViewMap = array(
        "ezembed" => "embed",
        "ezembedinline" => "embed-inline"
    );

    public function __construct( RendererInterface $renderer, LoggerInterface $logger = null )
    {
        parent::__construct( $renderer );
        $this->logger = $logger;
    }

    /**
     * Processes single embed element type (ezembed or ezembedinline)
     *
     * @param \DOMDocument $document
     * @param $tagName string name of the tag to extract
     * @param boolean $isInline
     */
    protected function processTag( DOMDocument $document, $tagName, $isInline )
    {
        /** @var $embed \DOMElement */
        foreach ( $document->getElementsByTagName( $tagName ) as $embed )
        {
            if ( !$viewType = $embed->getAttribute( "view" ) )
            {
                $viewType = $this->tagDefaultViewMap[$tagName];
            }

            $embedContent = null;
            $parameters = array(
                "params" => $this->extractConfiguration( $embed ),
                "view" => $viewType,
            );

            if ( $embed->hasAttribute( "ezxhtml:class" ) )
            {
                $parameters["class"] = $embed->getAttribute( "ezxhtml:class" );
            }

            if ( $embed->hasAttribute( "ezxhtml:align" ) )
            {
                $parameters["align"] = $embed->getAttribute( "ezxhtml:align" );
            }

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
                $parameters["id"] = $matches[2];
                $embedContent = $this->renderer->renderContentEmbed(
                    $matches[2],
                    $viewType,
                    $parameters,
                    $isInline
                );
            }
            else if ( $matches[1] === "ezlocation" )
            {
                $parameters["id"] = $matches[2];
                $embedContent = $this->renderer->renderLocationEmbed(
                    $matches[2],
                    $viewType,
                    $parameters,
                    $isInline
                );
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
     * Injects rendered payloads into embed elements
     *
     * @param \DOMDocument $document
     *
     * @return \DOMDocument
     */
    public function convert( DOMDocument $document )
    {
        $this->processTag( $document, 'ezembed', false );
        $this->processTag( $document, 'ezembedinline', true );

        return $document;
    }
}
