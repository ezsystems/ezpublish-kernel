<?php

/**
 * File containing the eZ\Publish\Core\FieldType\RichText\Converter\Render\Style class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license   For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText\Converter\Render;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use eZ\Publish\Core\FieldType\RichText\Converter;
use eZ\Publish\Core\FieldType\RichText\Converter\Render;
use eZ\Publish\Core\FieldType\RichText\RendererInterface;

/**
 * RichText Style converter injects rendered style payloads into style elements.
 */
class Style extends Render implements Converter
{
    /**
     * @var Converter
     */
    private $richTextConverter;

    /**
     * Style constructor.
     *
     * @param RendererInterface $renderer
     * @param Converter         $richTextConverter
     */
    public function __construct(RendererInterface $renderer, Converter $richTextConverter)
    {
        $this->richTextConverter = $richTextConverter;
        parent::__construct($renderer);
    }

    /**
     * Injects rendered payloads into Custom Style elements.
     *
     * @param \DOMDocument $document
     *
     * @return \DOMDocument
     */
    public function convert(DOMDocument $document)
    {
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('docbook', 'http://docbook.org/ns/docbook');
        $xpathExpression = '//docbook:ezstyle | //docbook:ezstyleinline';

        $styles = $xpath->query($xpathExpression);
        /** @var \DOMElement[] $stylesSorted */
        $stylesSorted = [];
        $maxDepth = 0;

        foreach ($styles as $style) {
            $depth = $this->getNodeDepth($style);
            if ($depth > $maxDepth) {
                $maxDepth = $depth;
            }
            $stylesSorted[$depth][] = $style;
        }

        ksort($stylesSorted, SORT_NUMERIC);
        foreach ($stylesSorted as $styles) {
            foreach ($styles as $style) {
                $this->processStyle($document, $style);
            }
        }

        return $document;
    }

    /**
     * Processes given template $style in a given $document.
     *
     * @param \DOMDocument $document
     * @param \DOMElement  $style
     */
    protected function processStyle(DOMDocument $document, DOMElement $style)
    {
        $content = null;
        $styleName = $style->getAttribute('name');
        $parameters = [
            'name' => $styleName,
            'content' => $this->saveNodeXML($style),
        ];

        if ($style->hasAttribute('ezxhtml:align')) {
            $parameters['align'] = $style->getAttribute('ezxhtml:align');
        }

        $content = $this->renderer->renderStyle(
            $styleName,
            $parameters,
            $style->localName === 'ezstyleinline'
        );

        if (isset($content)) {
            // If current tag is wrapped inside another Custom Style tag we can't use CDATA section
            // for its content as these can't be nested.
            // CDATA section will be used only for content of root wrapping tag, content of tags
            // inside it will be added as XML fragments.
            if ($this->isWrapped($style)) {
                $fragment = $document->createDocumentFragment();
                $fragment->appendXML($content);
                $style->parentNode->replaceChild($fragment, $style);
            } else {
                $payload = $document->createElement('ezpayload');
                $payload->appendChild($document->createCDATASection($content));
                $style->appendChild($payload);
            }
        }
    }

    /**
     * Returns if the given $node is wrapped inside another template node.
     *
     * @param \DOMNode $node
     *
     * @return bool
     */
    protected function isWrapped(DomNode $node)
    {
        while ($node = $node->parentNode) {
            if ($node->localName === 'ezstyle' || $node->localName === 'ezstyleinline') {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns depth of given $node in a DOMDocument.
     *
     * @param \DOMNode $node
     *
     * @return int
     */
    protected function getNodeDepth(DomNode $node)
    {
        // initial depth for top level elements (to avoid "ifs")
        $depth = -2;

        while ($node) {
            ++$depth;
            $node = $node->parentNode;
        }

        return $depth;
    }

    /**
     * Returns XML fragment string for given $node.
     *
     * @param \DOMNode $node
     *
     * @return string
     */
    protected function saveNodeXML(DOMNode $node)
    {
        $innerDoc = new DOMDocument();

        /** @var \DOMNode $child */
        foreach ($node->childNodes as $child) {
            $innerDoc->appendChild($innerDoc->importNode($child, true));
        }

        $convertedInnerDoc = $this->richTextConverter->convert($innerDoc);

        return trim($convertedInnerDoc ? $convertedInnerDoc->saveHTML() : $innerDoc->saveHTML());
    }
}
