<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter\Output\Html5 class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter\Output;

use eZ\Publish\Core\FieldType\XmlText\Converter,
    DOMDocument,
    XSLTProcessor;

/**
 * Converts internal
 */
class Html5 implements Converter
{
    /**
     * Path to stylesheet to use
     *
     * @var string
     */
    protected $stylesheet;

    /**
     * Constructor
     *
     * @param string $stylesheet Stylesheet to use for conversion
     */
    public function __construct( $stylesheet )
    {
        $this->stylesheet = $stylesheet;
    }

    /**
     * Convert $xmlString from internal representation to HTML5
     *
     * @param string $xmlString
     * @return string
     */
    public function convert( $xmlString )
    {
        $doc = new DOMDocument;
        $doc->load( $this->stylesheet );
        $xsl = new XSLTProcessor();
        $xsl->registerPHPFunctions();
        $xsl->importStyleSheet( $doc );
        $doc->loadXML( $xmlString );

        return $xsl->transformToXML( $doc );
    }

    public static function resolveUrl( $data )
    {
        return "http://ez.no/$data";
    }
}
