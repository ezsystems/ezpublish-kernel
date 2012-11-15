<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Input\EzSimplifiedXml class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Input;

use eZ\Publish\Core\FieldType\XmlText\Input,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    DOMDocument,
    XSLTProcessor;

class EzSimplifiedXml extends Input
{
    /**
     * Constructor
     *
     * @param string $xmlString The Simplified eZ XML content
     * @param string $schemaPath Path to XSD file
     * @param string $stylesheetPath Path to stylesheet file
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException if content does not validate
     */
    public function __construct( $xmlString, $schemaPath = null, $stylesheetPath = null )
    {
        if ( $schemaPath === null )
            $schemaPath = __DIR__ . "/Resources/schemas/ezsimplifiedxml.xsd";

        if ( $stylesheetPath === null )
            $stylesheetPath = __DIR__ . "/Resources/stylesheets/eZSimplifiedXml2eZXml.xsl";

        if ( !file_exists( $schemaPath ) )
            throw new InvalidArgumentException(
                "schemaPath",
                "Validation of XML content cannot be performed, file '$schemaPath' does not exist."
            );

        if ( !file_exists( $stylesheetPath ) )
            throw new InvalidArgumentException(
                "stylesheetPath",
                "Convertion of XML content cannot be performed, file '$stylesheetPath' does not exist."
            );

        $doc = new DOMDocument;
        $doc->load( $stylesheetPath );

        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet( $doc );

        libxml_use_internal_errors( true );
        libxml_clear_errors();
        $doc->loadXML( $xmlString );
        if ( !$doc->schemaValidate( $schemaPath ) )
        {
            $messages = array();

            foreach ( libxml_get_errors() as $error )
                $messages[] = trim( $error->message );

            throw new InvalidArgumentException(
                "xmlString",
                "Validation of XML content failed: " . join( "\n", $messages )
            );
        }

        $this->internalRepresentation = $xsl->transformToXML( $doc );
    }
}
