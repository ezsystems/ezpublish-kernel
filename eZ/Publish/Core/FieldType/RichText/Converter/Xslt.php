<?php

/**
 * File containing the eZ\Publish\Core\FieldType\RichText\Converter\Xslt class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText\Converter;

use eZ\Publish\Core\FieldType\RichText\XmlBase;
use eZ\Publish\Core\FieldType\RichText\Converter;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use DOMDocument;
use XSLTProcessor;
use RuntimeException;

/**
 * Converts DOMDocument objects using XSLT stylesheets.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\RichText\Converter\Xslt from EzPlatformRichTextBundle.
 */
class Xslt extends XmlBase implements Converter
{
    /**
     * Path to stylesheet to use.
     *
     * @var string
     */
    protected $stylesheet;

    /**
     * Array of XSL stylesheets to add to the main one, grouped by priority.
     *
     * @var array
     */
    protected $customStylesheets = [];

    /**
     * Constructor.
     *
     * @param string $stylesheet Stylesheet to use for conversion
     * @param array $customStylesheets Array of XSL stylesheets. Each entry consists in a hash having "path" and "priority" keys.
     */
    public function __construct($stylesheet, array $customStylesheets = [])
    {
        $this->stylesheet = $stylesheet;

        // Grouping stylesheets by priority.
        foreach ($customStylesheets as $customStylesheet) {
            $this->customStylesheets[$customStylesheet['priority']][] = $customStylesheet['path'];
        }
    }

    /**
     * Returns the XSLTProcessor to use to transform internal XML to HTML5.
     *
     * @throws \RuntimeException
     *
     * @return \XSLTProcessor
     */
    protected function getXSLTProcessor()
    {
        if (isset($this->xsltProcessor)) {
            return $this->xsltProcessor;
        }

        $xslDoc = $this->loadFile($this->stylesheet);

        // Now loading custom xsl stylesheets dynamically.
        // According to XSL spec, each <xsl:import> tag MUST be loaded BEFORE any other element.
        $insertBeforeEl = $xslDoc->documentElement->firstChild;
        foreach ($this->getSortedCustomStylesheets() as $stylesheet) {
            if (!file_exists($stylesheet)) {
                throw new RuntimeException("Cannot find XSL stylesheet for RichText rendering: $stylesheet");
            }

            $newEl = $xslDoc->createElement('xsl:import');
            $hrefAttr = $xslDoc->createAttribute('href');

            // Prevents showing XSLTProcessor::importStylesheet() warning on Windows file system
            $hrefAttr->value = str_replace('\\', '/', $stylesheet);

            $newEl->appendChild($hrefAttr);
            $xslDoc->documentElement->insertBefore($newEl, $insertBeforeEl);
        }
        // Now reload XSL DOM to "refresh" it.
        $xslDoc->loadXML($xslDoc->saveXML());

        $this->xsltProcessor = new XSLTProcessor();
        $this->xsltProcessor->importStyleSheet($xslDoc);
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
        $sortedStylesheets = [];
        ksort($this->customStylesheets);
        foreach ($this->customStylesheets as $stylesheets) {
            $sortedStylesheets = array_merge($sortedStylesheets, $stylesheets);
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
    public function convert(DOMDocument $document)
    {
        if (!file_exists($this->stylesheet)) {
            throw new InvalidArgumentException(
                'stylesheetPath',
                "Conversion of XML document cannot be performed, file '{$this->stylesheet}' does not exist."
            );
        }

        $processor = $this->getXSLTProcessor();

        $this->startRecordingErrors();

        $document = $processor->transformToDoc($document);

        $errors = $this->collectErrors();

        if (!empty($errors)) {
            throw new InvalidArgumentException(
                '$xmlDoc',
                'Transformation of XML content failed: ' . implode("\n", $errors)
            );
        }

        return $document;
    }
}
