<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter\Html5 class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use DOMDocument;
use XSLTProcessor;
use RuntimeException;

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
     * Array of XSL stylesheets to add to the main one, grouped by priority.
     *
     * @var array
     */
    protected $customStylesheets = array();

    /**
     * @var \XSLTProcessor
     */
    protected $xsltProcessor;

    /**
     * Array of converters that needs to be called before actual processing.
     *
     * @var \eZ\Publish\Core\FieldType\XmlText\Converter[]
     */
    private $preConverters;

    /**
     * Constructor
     *
     * @param string $stylesheet Stylesheet to use for conversion
     * @param array $customStylesheets Array of XSL stylesheets. Each entry consists in a hash having "path" and "priority" keys.
     * @param \eZ\Publish\Core\FieldType\XmlText\Converter[] $preConverters Array of pre-converters
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function __construct( $stylesheet, array $customStylesheets = array(), array $preConverters = array() )
    {
        $this->stylesheet = $stylesheet;

        // Grouping stylesheets by priority.
        foreach ( $customStylesheets as $stylesheet )
        {
            if ( !isset( $this->customStylesheets[$stylesheet['priority']] ) )
            {
                $this->customStylesheets[$stylesheet['priority']] = array();
            }

            $this->customStylesheets[$stylesheet['priority']][] = $stylesheet['path'];
        }

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
     * Adds a pre-converter to the list.
     * Use a pre-converter when you need some processing before XSLT transformation (e.g. for custom tags).
     *
     * @param Converter $preConverter
     */
    public function addPreConverter( Converter $preConverter )
    {
        $this->preConverters[] = $preConverter;
    }

    /**
     * @return array|\eZ\Publish\Core\FieldType\XmlText\Converter[]
     */
    public function getPreConverters()
    {
        return $this->preConverters;
    }

    /**
     * Returns the XSLTProcessor to use to transform internal XML to HTML5.
     *
     * @return \XSLTProcessor
     */
    protected function getXSLTProcessor()
    {
        if ( isset( $this->xsltProcessor ) )
        {
            return $this->xsltProcessor;
        }

        $xslDoc = new DOMDocument;
        $xslDoc->load( $this->stylesheet );

        // Now loading custom xsl stylesheets dynamically.
        // According to XSL spec, each <xsl:import> tag MUST be loaded BEFORE any other element.
        $insertBeforeEl = $xslDoc->documentElement->firstChild;
        foreach ( $this->getSortedCustomStylesheets() as $stylesheet )
        {
            if ( !file_exists( $stylesheet ) )
            {
                throw new RuntimeException( "Cannot find XSL stylesheet for XMLText rendering: $stylesheet" );
            }

            $newEl = $xslDoc->createElement( 'xsl:import' );
            $hrefAttr = $xslDoc->createAttribute( 'href' );
            $hrefAttr->value = $stylesheet;
            $newEl->appendChild( $hrefAttr );
            $xslDoc->documentElement->insertBefore( $newEl, $insertBeforeEl );
        }
        // Now reload XSL DOM to "refresh" it.
        $xslDoc->loadXML( $xslDoc->saveXML() );

        $this->xsltProcessor = new XSLTProcessor();
        $this->xsltProcessor->importStyleSheet( $xslDoc );
        $this->xsltProcessor->registerPHPFunctions();
        return $this->xsltProcessor;
    }

    /**
     * Returns custom stylesheets to load, sorted.
     * The order is from the lowest priority to the highest since in case of a conflict,
     * the last loaded XSL template always wins.
     *
     * @return array
     */
    private function getSortedCustomStylesheets()
    {
        $sortedStylesheets = array();
        ksort( $this->customStylesheets );
        foreach ( $this->customStylesheets as $stylesheets )
        {
            $sortedStylesheets = array_merge( $sortedStylesheets, $stylesheets );
        }

        return $sortedStylesheets;
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
        foreach ( $this->getPreConverters() as $preConverter )
        {
            $preConverter->convert( $xmlDoc );
        }

        $xsl = $this->getXSLTProcessor();
        return $xsl->transformToXML( $xmlDoc );
    }
}
