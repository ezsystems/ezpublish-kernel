<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\XsltConverter class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
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
     * Array of converters that needs to be called before actual processing.
     *
     * @var \eZ\Publish\Core\FieldType\XmlText\Converter[]
     */
    protected $preConverters;

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
     * @param \eZ\Publish\Core\FieldType\XmlText\Converter[] $preConverters Array of pre-converters
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
                    "eZ\\Publish\\Core\\FieldType\\XmlText\\XsltConverter",
                    $preConverter
                );
        }

        $this->preConverters = $preConverters;
    }

    /**
     * @param LibXMLError $error
     *
     * @return string
     */
    protected function formatLibXmlError( LibXMLError $error )
    {
        return sprintf(
            "%s in %d:%d: %s.",
            $this->errorTypes[$error->level],
            $error->line,
            $error->column,
            trim( $error->message )
        );
    }

    /**
     * Convert $xmlDoc from internal representation DOMDocument to HTML5
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if stylesheet is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if document does not transform
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

        if ( !file_exists( $this->stylesheet ) )
        {
            throw new InvalidArgumentException(
                "schemaPath",
                "Conversion of XML document cannot be performed, file '{$this->stylesheet}' does not exist."
            );
        }

        $xslDoc = new DOMDocument;
        $xslDoc->load( $this->stylesheet );
        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet( $xslDoc );

        // We want to handle the occurred errors ourselves.
        $oldSetting = libxml_use_internal_errors( true );

        libxml_clear_errors();
        $xmlString = $xsl->transformToXML( $xmlDoc );

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

        return $xmlString;
    }
}
