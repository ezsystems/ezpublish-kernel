<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter\Input\EzSimplifiedXml class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter\Input;

use eZ\Publish\Core\FieldType\XmlText\Converter,
    DOMDocument,
    Exception,
    XSLTProcessor;

class EzSimplifiedXml implements Converter
{
    /**
     * Path to XSD
     *
     * @var string
     */
    protected $schemaPath;

    /**
     * Path to stylesheet
     *
     * @var string
     */
    protected $stylesheetPath;

    /**
     * Constructor
     *
     * @param string $schemaPath Path to XSD
     * @param string $stylesheetPath Path to stylesheet
     *
     */
    public function __construct( $schemaPath, $stylesheetPath )
    {
        $this->schemaPath = $schemaPath;
        $this->stylesheetPath = $stylesheetPath;
    }

    /**
     * Convert $xmlString from simplified eZ XML to internal representation
     *
     * @param string $xmlString
     * @return string
     */
    public function convert( $xmlString )
    {
        $doc = new DOMDocument;
        $doc->load( $this->stylesheetPath );

        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet( $doc );

        libxml_use_internal_errors( true );
        libxml_clear_errors();
        $doc->loadXML( $xmlString );
        if ( !$doc->schemaValidate( $this->schemaPath ) )
        {
            $messages = array();

            foreach ( libxml_get_errors() as $error )
                $messages[] = trim( $error->message );

            throw new Exception( join( "\n", $messages ) );
        }

        return $xsl->transformToXML( $doc );
    }
}
