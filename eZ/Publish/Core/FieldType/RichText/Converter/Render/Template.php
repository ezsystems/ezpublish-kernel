<?php

/**
 * File containing the eZ\Publish\Core\FieldType\RichText\Converter\Render\Template class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText\Converter\Render;

use eZ\Publish\Core\FieldType\RichText\Converter;
use eZ\Publish\Core\FieldType\RichText\Converter\Render;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * RichText Template converter injects rendered template payloads into template elements.
 */
class Template extends Render implements Converter
{
    /**
     * Injects rendered payloads into template tag elements.
     *
     * @param \DOMDocument $document
     *
     * @return \DOMDocument
     */
    public function convert(DOMDocument $document)
    {
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('docbook', 'http://docbook.org/ns/docbook');
        $xpathExpression = '//docbook:eztemplate | //docbook:eztemplateinline';

        $tags = $xpath->query($xpathExpression);
        /** @var \DOMElement[] $tagsSorted */
        $tagsSorted = array();
        $maxDepth = 0;

        foreach ($tags as $tag) {
            $depth = $this->getNodeDepth($tag);
            if ($depth > $maxDepth) {
                $maxDepth = $depth;
            }
            $tagsSorted[$depth][] = $tag;
        }

        krsort($tagsSorted, SORT_NUMERIC);

        foreach ($tagsSorted as $tags) {
            foreach ($tags as $tag) {
                $this->processTag($document, $tag);
            }
        }

        return $document;
    }

    /**
     * Processes given template $tag in a given $document.
     *
     * @param \DOMDocument $document
     * @param \DOMElement $tag
     */
    protected function processTag(DOMDocument $document, DOMElement $tag)
    {
        $content = null;
        $tagName = $tag->getAttribute('name');
        $parameters = array(
            'name' => $tagName,
            'params' => $this->extractConfiguration($tag),
        );

        if ($tag->getElementsByTagName('ezcontent')->length > 0) {
            $parameters['content'] = $this->saveNodeXML(
                $tag->getElementsByTagName('ezcontent')->item(0)
            );
        }

        if ($tag->hasAttribute('xlink:align')) {
            $parameters['align'] = $tag->getAttribute('xlink:align');
        }

        $content = $this->renderer->renderTag(
            $tagName,
            $parameters,
            $tag->localName === 'eztemplateinline'
        );

        if (isset($content)) {
            // If current tag is wrapped inside another template tag we can't use CDATA section
            // for its content as these can't be nested.
            // CDATA section will be used only for content of root wrapping tag, content of tags
            // inside it will be added as XML fragments.
            if ($this->isWrapped($tag)) {
                $fragment = $document->createDocumentFragment();
                $fragment->appendXML($content);
                $tag->parentNode->replaceChild($fragment, $tag);
            } else {
                $payload = $document->createElement('ezpayload');
                $payload->appendChild($document->createCDATASection($content));
                $tag->appendChild($payload);
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
            if ($node->localName === 'eztemplate' || $node->localName === 'eztemplateinline') {
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
        $xmlString = '';

        /** @var \DOMNode $child */
        foreach ($node->childNodes as $child) {
            $xmlString .= $node->ownerDocument->saveXML($child);
        }

        return $xmlString;
    }
}
