<?php
/**
 * File containing the eZ\Publish\Core\FieldType\RichText\Converter\Render\Template class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RichText\Converter\Render;

use eZ\Publish\Core\FieldType\RichText\Converter;
use eZ\Publish\Core\FieldType\RichText\Converter\Render;
use DOMDocument;
use DOMElement;

/**
 * RichText Template converter injects rendered template payloads into template elements.
 */
class Template extends Render implements Converter
{
    /**
     * Processes single embed element type (ezembed or ezembedinline)
     *
     * @param \DOMDocument $document
     * @param $tagName string name of the tag to extract
     */
    protected function processTag( DOMDocument $document, $tagName )
    {
        /** @var $template \DOMElement */
        foreach ( $document->getElementsByTagName( $tagName ) as $template )
        {
            $content = null;
            $tagName = $template->getAttribute( "name" );
            $parameters = array(
                "name" => $tagName,
                "params" => $this->extractConfiguration( $template ),
                "content" => $template->getElementsByTagName( "ezcontent" )->length ?
                    $this->saveNodeXML( $template->getElementsByTagName( "ezcontent" )->item( 0 ) ) :
                    null
            );
            if ( $template->hasAttribute( "xlink:align" ) )
            {
                $parameters["align"] = $template->getAttribute( "xlink:align" );
            }

            $content = $this->renderer->renderTag( $tagName, $parameters );

            if ( isset( $content ) )
            {
                $payload = $document->createElement( "ezpayload" );
                $payload->appendChild( $document->createCDATASection( $content ) );
                $template->appendChild( $payload );
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
        $this->processTag( $document, 'eztemplate' );
        $this->processTag( $document, 'eztemplateinline' );

        return $document;
    }
}
