<?php

/**
 * File containing the eZ\Publish\Core\FieldType\RichText\Converter\Render\Embed class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText\Converter\Render;

use eZ\Publish\Core\FieldType\RichText\RendererInterface;
use eZ\Publish\Core\FieldType\RichText\Converter;
use eZ\Publish\Core\FieldType\RichText\Converter\Render;
use Psr\Log\LoggerInterface;
use DOMDocument;
use DOMElement;

/**
 * RichText Embed converter injects rendered embed payloads into embed elements.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\RichText\Converter\Render\Embed from EzPlatformRichTextBundle.
 */
class Embed extends Render implements Converter
{
    /** @var null|\Psr\Log\LoggerInterface */
    protected $logger;

    /**
     * Maps embed tag names to their default views.
     *
     * @var array
     */
    protected $tagDefaultViewMap = [
        'ezembed' => 'embed',
        'ezembedinline' => 'embed-inline',
    ];

    /**
     * Maps Docbook target to HTML target.
     *
     * @var array
     */
    protected $docbookToHtmlTargetMap = [
        'new' => '_blank',
        'replace' => '_self',
    ];

    public function __construct(RendererInterface $renderer, LoggerInterface $logger = null)
    {
        parent::__construct($renderer);
        $this->logger = $logger;
    }

    /**
     * Processes single embed element type (ezembed or ezembedinline).
     *
     * @param \DOMDocument $document
     * @param $tagName string name of the tag to extract
     * @param bool $isInline
     */
    protected function processTag(DOMDocument $document, $tagName, $isInline)
    {
        /** @var $embed \DOMElement */
        foreach ($document->getElementsByTagName($tagName) as $embed) {
            $embedContent = null;
            $parameters = $this->extractParameters($embed, $tagName);
            $resourceReference = $embed->getAttribute('xlink:href');

            if (empty($resourceReference)) {
                if (isset($this->logger)) {
                    $this->logger->error("Could not embed resource: empty 'xlink:href' attribute");
                }
            } elseif (0 === preg_match('~^(ezcontent|ezlocation)://(.*)$~', $resourceReference, $matches)) {
                if (isset($this->logger)) {
                    $this->logger->error(
                        "Could not embed resource: unhandled resource reference '{$resourceReference}'"
                    );
                }
            } elseif ($matches[1] === 'ezcontent') {
                $parameters['id'] = $matches[2];
                $embedContent = $this->renderer->renderContentEmbed(
                    $parameters['id'],
                    $parameters['viewType'],
                    [
                        'embedParams' => $parameters,
                    ],
                    $isInline
                );
            } elseif ($matches[1] === 'ezlocation') {
                $parameters['id'] = $matches[2];
                $embedContent = $this->renderer->renderLocationEmbed(
                    $parameters['id'],
                    $parameters['viewType'],
                    [
                        'embedParams' => $parameters,
                    ],
                    $isInline
                );
            }

            if (isset($embedContent)) {
                $payload = $document->createElement('ezpayload');
                $payload->appendChild($document->createCDATASection($embedContent));
                $embed->appendChild($payload);
            }
        }
    }

    /**
     * Extracts parameters from embed element.
     *
     * @param \DOMElement $embed
     * @param $tagName string name of the tag to extract
     *
     * @return array
     */
    protected function extractParameters(DOMElement $embed, $tagName)
    {
        if (!$viewType = $embed->getAttribute('view')) {
            $viewType = $this->tagDefaultViewMap[$tagName];
        }

        $class = $embed->getAttribute('ezxhtml:class');
        $align = $embed->getAttribute('ezxhtml:align');
        $linkParameters = $this->extractLinkParameters($embed);
        $configuration = $this->extractConfiguration($embed);

        // Setting template parameters only if not empty
        $parameters = [
            'viewType' => $viewType,
        ];

        if (!empty($class)) {
            $parameters['class'] = $class;
        }

        if (!empty($align)) {
            $parameters['align'] = $align;
        }

        if (!empty($linkParameters)) {
            $parameters['link'] = $linkParameters;
        }

        if (!empty($configuration)) {
            $parameters['config'] = $configuration;
        }

        return $parameters;
    }

    /**
     * Extracts link parameters from embed element.
     *
     * @param \DOMElement $embed
     *
     * @return array
     */
    protected function extractLinkParameters(DOMElement $embed)
    {
        $links = $embed->getElementsByTagName('ezlink');

        if ($links->length !== 1) {
            return null;
        }

        /** @var \DOMElement $link */
        $link = $links->item(0);

        $hrefResolved = $link->getAttribute('href_resolved');

        if (empty($hrefResolved)) {
            $this->logger->error('Could not create link parameters: resolved embed link is missing');

            return null;
        }

        $href = $link->getAttribute('xlink:href');
        $target = $link->getAttribute('xlink:show');
        $target = $this->mapLinkTarget($target);
        $title = $link->getAttribute('xlink:title');
        $id = $link->getAttribute('xml:id');
        $class = $link->getAttribute('ezxhtml:class');

        if (strpos($href, 'ezcontent://') === 0) {
            $resourceType = 'CONTENT';
            $resourceId = substr($href, strlen('ezcontent://'));
        } elseif (strpos($href, 'ezlocation://') === 0) {
            $resourceType = 'LOCATION';
            $resourceId = substr($href, strlen('ezlocation://'));
        } else {
            // If link is not Content or Location based, it must be an URL (Url field type) link
            $resourceType = 'URL';
            // ATM there is no way to find out the URL's ID here.
            // The whole implementation is actually lacking:
            // UrlService which would be used here and in Url and RichText field type's external storage,
            // but also for admin UI.
            // With it resolving Url links in the RichText external storage when loading should be removed.
            // Data should be returned as is, and resolving should happen when needed, which is:
            // - in Link converter for links
            // - here for embeds
            $resourceId = null;
        }

        $fragmentPosition = strpos($resourceId, '#');

        if ($fragmentPosition !== false) {
            $resourceFragmentIdentifier = substr($resourceId, $fragmentPosition + 1);
            $resourceId = substr($resourceId, 0, $fragmentPosition);
        }

        $parameters = [
            'href' => $hrefResolved,
            'resourceType' => $resourceType,
            'resourceId' => $resourceId,
            'wrapped' => $this->isLinkWrapped($embed),
        ];

        if (!empty($resourceFragmentIdentifier)) {
            $parameters['resourceFragmentIdentifier'] = $resourceFragmentIdentifier;
        }

        if (!empty($target)) {
            $parameters['target'] = $target;
        }

        if (!empty($title)) {
            $parameters['title'] = $title;
        }

        if (!empty($id)) {
            $parameters['id'] = $id;
        }

        if (!empty($class)) {
            $parameters['class'] = $class;
        }

        return $parameters;
    }

    /**
     * Converts Docbook target to HTML target.
     *
     * @param string $docbookLinkTarget
     */
    protected function mapLinkTarget($docbookLinkTarget)
    {
        if (isset($this->docbookToHtmlTargetMap[$docbookLinkTarget])) {
            return $this->docbookToHtmlTargetMap[$docbookLinkTarget];
        }

        return null;
    }

    /**
     * Returns boolean signifying if the embed is contained in a link element of not.
     *
     * After EmbedLinking converter pass this should be possible only for inline level embeds.
     *
     * @param \DOMElement $element
     *
     * @return bool
     */
    protected function isLinkWrapped(DOMElement $element)
    {
        $parentNode = $element->parentNode;

        if ($parentNode instanceof DOMDocument) {
            return false;
        } elseif ($parentNode->localName === 'link') {
            $childCount = 0;

            /** @var \DOMText|\DOMElement $node */
            foreach ($parentNode->childNodes as $node) {
                if (!($node->nodeType === XML_TEXT_NODE && $node->isWhitespaceInElementContent())) {
                    $childCount += 1;
                }
            }

            if ($childCount === 1) {
                return false;
            } else {
                return true;
            }
        }

        return $this->isLinkWrapped($parentNode);
    }

    /**
     * Injects rendered payloads into embed elements.
     *
     * @param \DOMDocument $document
     *
     * @return \DOMDocument
     */
    public function convert(DOMDocument $document)
    {
        $this->processTag($document, 'ezembed', false);
        $this->processTag($document, 'ezembedinline', true);

        return $document;
    }
}
