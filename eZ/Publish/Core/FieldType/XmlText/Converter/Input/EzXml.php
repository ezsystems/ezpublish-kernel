<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter\Input\EzXml class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter\Input;

use eZ\Publish\Core\FieldType\XmlText\Converter,
    DOMDocument,
    Exception;

class EzXml implements Converter
{
    /**
     * Path to XSD
     *
     * @var string
     */
    protected $schemaPath;

    /**
     * Constructor
     *
     * @param string $schemaPath Path to XSD
     *
     */
    public function __construct( $schemaPath )
    {
        $this->schemaPath = $schemaPath;
    }

    /**
     * Convert $xmlString from eZ XML to internal representation
     *
     * @param string $xmlString
     * @return string
     */
    public function convert( $xmlString )
    {
        $doc = new DOMDocument;
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

        return $xmlString;
    }
}
