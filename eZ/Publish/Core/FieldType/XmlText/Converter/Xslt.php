<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter\Xslt class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use DOMDocument;
use XSLTProcessor;
use LibXMLError;

/**
 *
 */
class Xslt implements Converter
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
     * Textual mapping for libxml error types.
     *
     * @var array
     */
    protected $errorTypes = array(
        LIBXML_ERR_WARNING => 'Warning',
        LIBXML_ERR_ERROR => 'Error',
        LIBXML_ERR_FATAL => 'Fatal error',
    );

    /**
     * Constructor
     *
     * @param string $stylesheet Stylesheet to use for conversion
     * @param array $customStylesheets Array of XSL stylesheets. Each entry consists in a hash having "path" and "priority" keys.
     */
    public function __construct( $stylesheet, array $customStylesheets = array() )
    {
        $this->stylesheet = $stylesheet;

        // Grouping stylesheets by priority.
        foreach ( $customStylesheets as $customStylesheet )
        {
            $this->customStylesheets[$customStylesheet['priority']][] = $customStylesheet['path'];
        }
    }

    /**
     * @param LibXMLError $error
     *
     * @return string
     */
    protected function formatLibXmlError( LibXMLError $error )
    {
        return sprintf(
            "%s in %d:%d: %s",
            $this->errorTypes[$error->level],
            $error->line,
            $error->column,
            trim( $error->message )
        );
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
    protected function getSortedCustomStylesheets()
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
     * Performs conversion of the given $document using XSLT stylesheet.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if stylesheet is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if document does not transform
     *
     * @param \DOMDocument $document
     *
     * @return \DOMDocument
     */
    public function convert( DOMDocument $document )
    {
        if ( !file_exists( $this->stylesheet ) )
        {
            throw new InvalidArgumentException(
                "stylesheetPath",
                "Conversion of XML document cannot be performed, file '{$this->stylesheet}' does not exist."
            );
        }

        $xslDoc = new DOMDocument;
        $xslDoc->load( $this->stylesheet );
        $processor = $this->getXSLTProcessor();

        // We want to handle the occurred errors ourselves.
        $oldSetting = libxml_use_internal_errors( true );

        libxml_clear_errors();
        $document = $processor->transformToDoc( $document );

        // Get all errors
        $xmlErrors = libxml_get_errors();
        $errors = array();
        foreach ( $xmlErrors as $error )
        {
            $errors[] = $this->formatLibXmlError( $error );
        }
        libxml_clear_errors();
        libxml_use_internal_errors( $oldSetting );

        if ( !empty( $errors ) )
        {
            throw new InvalidArgumentException(
                "\$xmlDoc",
                "Transformation of XML content failed: " . join( "\n", $errors )
            );
        }

        return $document;
    }
}
