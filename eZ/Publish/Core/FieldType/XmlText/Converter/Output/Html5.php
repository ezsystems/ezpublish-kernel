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
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
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
     * Array of converters that needs to be called before actual processing.
     *
     * @var array[eZ\Publish\Core\FieldType\XmlText\Converter]
     */
    protected $preConverters;

    /**
     * Constructor
     *
     * @param string $stylesheet Stylesheet to use for conversion
     * @param array[eZ\Publish\Core\FieldType\XmlText\Converter] $preConverters Array of pre-converters
     */
    public function __construct( $stylesheet, array $preConverters = array() )
    {
        $this->stylesheet = $stylesheet;

        foreach ( $preConverters as $preConverter )
        {
            if ( !$preConverter instanceof Converter )
                throw new InvalidArgumentType(
                    '$preConverters',
                    "array[eZ\Publish\\Core\\FieldType\\XmlText\\Converter]",
                    $preConverter
                );
        }

        $this->preConverters = $preConverters;
    }

    /**
     * Convert $xmlString from internal representation to HTML5
     *
     * @param string $xmlString
     * @return string
     */
    public function convert( $xmlString )
    {
        foreach ( $this->preConverters as $preConverter )
        {
            $xmlString = $preConverter->convert( $xmlString );
        }

        $doc = new DOMDocument;
        $doc->load( $this->stylesheet );
        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet( $doc );
        $doc->loadXML( $xmlString );

        foreach ( $doc->getElementsByTagName( "link" ) as $link )
        {
            $link->setAttribute( "url", "http://ez.no/url/id/" . $link->getAttribute( "url_id" ) );
        }

        return $xsl->transformToXML( $doc );
    }
}
