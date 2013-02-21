<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter\Html5 class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use DOMDocument;
use XSLTProcessor;

/**
 * Converts internal XmlText representation to HTML5
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
     * @var \eZ\Publish\Core\FieldType\XmlText\Converter[]
     */
    protected $preConverters;

    /**
     * Constructor
     *
     * @param string $stylesheet Stylesheet to use for conversion
     * @param \eZ\Publish\Core\FieldType\XmlText\Converter\Output[] $preConverters Array of pre-converters
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function __construct( $stylesheet, array $preConverters = array() )
    {
        $this->stylesheet = $stylesheet;

        foreach ( $preConverters as $preConverter )
        {
            if ( !$preConverter instanceof Converter )
                throw new InvalidArgumentType(
                    '$preConverters',
                    "eZ\\Publish\\Core\\FieldType\\XmlText\\Converter[]",
                    $preConverter
                );
        }

        $this->preConverters = $preConverters;
    }

    /**
     * Convert $xmlDoc from internal representation DOMDocument to HTML5
     *
     * @param \DOMDocument $xmlDoc
     *
     * @return string
     */
    public function convert( DOMDocument $xmlDoc )
    {
        foreach ( $this->preConverters as $preConverter )
        {
            $preConverter->convert( $xmlDoc );
        }

        $xslDoc = new DOMDocument;
        $xslDoc->load( $this->stylesheet );
        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet( $xslDoc );

        return $xsl->transformToXML( $xmlDoc );
    }
}
