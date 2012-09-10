<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Input\Converter\EzSimplifiedXml class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Input\Converter;

use DOMDocument,
    Exception,
    XSLTProcessor;

class EzSimplifiedXml
{
    public function convert( $xmlString )
    {
        $doc = new DOMDocument;
        // @todo: inject stylesheet path
        $doc->load( __DIR__ . "/stylesheets/eZSimplifiedXml2eZXml.xsl" );

        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet( $doc );

        libxml_use_internal_errors( true );
        libxml_clear_errors();
        $doc->loadXML( $xmlString );
        // @todo: inject schema path
        if ( !$doc->schemaValidate( __DIR__ . "/schemas/ezsimplifiedxml.xsd" ) )
        {
            $messages = array();

            foreach ( libxml_get_errors() as $error )
                $messages[] = trim( $error->message );

            throw new Exception( join( "\n", $messages ) );
        }

        return $xsl->transformToXML( $doc );
    }
}
