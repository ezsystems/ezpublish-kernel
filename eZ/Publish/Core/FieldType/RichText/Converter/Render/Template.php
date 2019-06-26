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
use eZ\Publish\Core\FieldType\RichText\RendererInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * RichText Template converter injects rendered template payloads into template elements.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\RichText\Converter\Render\Template from EzPlatformRichTextBundle.
 */
class Template extends Render implements Converter
{
    /** @var \eZ\Publish\Core\FieldType\RichText\Converter */
    private $richTextConverter;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * Style constructor.
     *
     * @param \eZ\Publish\Core\FieldType\RichText\RendererInterface $renderer
     * @param \eZ\Publish\Core\FieldType\RichText\Converter $richTextConverter
     */
    public function __construct(
        RendererInterface $renderer,
        Converter $richTextConverter,
        LoggerInterface $logger = null
    ) {
        $this->richTextConverter = $richTextConverter;
        $this->logger = $logger ?? new NullLogger();

        parent::__construct($renderer);
    }

    /**
     * Injects rendered payloads into template elements.
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

        $templates = $xpath->query($xpathExpression);
        /** @var \DOMElement[] $templatesSorted */
        $templatesSorted = [];
        $maxDepth = 0;

        foreach ($templates as $template) {
            $depth = $this->getNodeDepth($template);
            if ($depth > $maxDepth) {
                $maxDepth = $depth;
            }
            $templatesSorted[$depth][] = $template;
        }

        ksort($templatesSorted, SORT_NUMERIC);

        foreach ($templatesSorted as $templates) {
            foreach ($templates as $template) {
                $this->processTemplate($document, $template);
            }
        }

        return $document;
    }

    /**
     * Processes given template $template in a given $document.
     *
     * @param \DOMDocument $document
     * @param \DOMElement $template
     */
    protected function processTemplate(DOMDocument $document, DOMElement $template)
    {
        $content = null;
        $templateName = $template->getAttribute('name');
        $templateType = $template->hasAttribute('type') ? $template->getAttribute('type') : 'tag';
        $parameters = [
            'name' => $templateName,
            'params' => $this->extractConfiguration($template),
        ];

        if ($template->getElementsByTagName('ezcontent')->length > 0) {
            $contentNode = $template->getElementsByTagName('ezcontent')->item(0);
            switch ($templateType) {
                case 'style':
                    $parameters['content'] = $this->getCustomStyleContent($contentNode);
                    break;
                case 'tag':
                default:
                    $parameters['content'] = $this->getCustomTagContent($contentNode);
                    break;
            }
        }

        if ($template->hasAttribute('ezxhtml:align')) {
            $parameters['align'] = $template->getAttribute('ezxhtml:align');
        }

        $content = $this->renderer->renderTemplate(
            $templateName,
            $templateType,
            $parameters,
            $template->localName === 'eztemplateinline'
        );

        if (isset($content)) {
            // If current tag is wrapped inside another template tag we can't use CDATA section
            // for its content as these can't be nested.
            // CDATA section will be used only for content of root wrapping tag, content of tags
            // inside it will be added as XML fragments.
            if ($this->isWrapped($template)) {
                $fragment = $document->createDocumentFragment();
                $fragment->appendXML($content);
                $template->parentNode->replaceChild($fragment, $template);
            } else {
                $payload = $document->createElement('ezpayload');
                $payload->appendChild($document->createCDATASection($content));
                $template->appendChild($payload);
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
        return $this->getCustomTagContent($node);
    }

    /**
     * Returns XML fragment string for given converted $node.
     *
     * @param \DOMNode $node
     *
     * @return string
     */
    protected function getCustomStyleContent(DOMNode $node)
    {
        $innerDoc = new DOMDocument();

        /** @var \DOMNode $child */
        foreach ($node->childNodes as $child) {
            $newNode = $innerDoc->importNode($child, true);
            if ($newNode === false) {
                $this->logger->warning(
                    "Failed to import Custom Style content of node '{$child->getNodePath()}'"
                );
            }
            $innerDoc->appendChild($newNode);
        }

        $convertedInnerDoc = $this->richTextConverter->convert($innerDoc);

        return trim($convertedInnerDoc->saveHTML());
    }

    /**
     * Returns XML fragment string for given $node.
     *
     * @param \DOMNode $node
     *
     * @return string
     */
    protected function getCustomTagContent(DOMNode $node)
    {
        $xmlString = '';

        /** @var \DOMNode $child */
        foreach ($node->childNodes as $child) {
            $xmlString .= $node->ownerDocument->saveXML($child);
        }

        return $xmlString;
    }
}
