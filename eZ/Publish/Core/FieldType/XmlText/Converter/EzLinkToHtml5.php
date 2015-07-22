<?php

/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter\EzLinkToHtml5 class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\FieldType\XmlText\Converter;
use eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Psr\Log\LoggerInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException as APIUnauthorizedException;
use DOMXPath;
use DOMElement;
use DOMDocument;

class EzLinkToHtml5 implements Converter
{
    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter
     */
    protected $urlAliasRouter;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(LocationService $locationService, ContentService $contentService, UrlAliasRouter $urlAliasRouter, LoggerInterface $logger = null)
    {
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->urlAliasRouter = $urlAliasRouter;
        $this->logger = $logger;
    }

    /**
     * Converts internal links (eznode:// and ezobject://) to URLs.
     *
     * @param \DOMDocument $xmlDoc
     *
     * @return string|null
     */
    public function convert(DOMDocument $xmlDoc)
    {
        $xpath = new DOMXPath($xmlDoc);

        $elements = $xpath->query('//link|//embed|//embed-inline');

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $location = null;

            if ($this->elementHasAttribute($element, 'object_id')) {
                try {
                    $contentInfo = $this->contentService->loadContentInfo(
                        $this->getElementAttribute($element, 'object_id')
                    );
                    $location = $this->locationService->loadLocation($contentInfo->mainLocationId);
                } catch (APINotFoundException $e) {
                    if ($this->logger) {
                        $this->logger->warning(
                            'While generating links for xmltext, could not locate ' .
                            'Content object with ID ' . $this->getElementAttribute($element, 'object_id')
                        );
                    }
                } catch (APIUnauthorizedException $e) {
                    if ($this->logger) {
                        $this->logger->notice(
                            'While generating links for xmltext, unauthorized to load ' .
                            'Content object with ID ' . $this->getElementAttribute($element, 'object_id')
                        );
                    }
                }
            }

            if ($this->elementHasAttribute($element, 'node_id')) {
                try {
                    $location = $this->locationService->loadLocation(
                        $this->getElementAttribute($element, 'node_id')
                    );
                } catch (APINotFoundException $e) {
                    if ($this->logger) {
                        $this->logger->warning(
                            'While generating links for xmltext, could not locate ' .
                            'Location with ID ' . $this->getElementAttribute($element, 'node_id')
                        );
                    }
                } catch (APIUnauthorizedException $e) {
                    if ($this->logger) {
                        $this->logger->notice(
                            'While generating links for xmltext, unauthorized to load ' .
                            'Location with ID ' . $this->getElementAttribute($element, 'node_id')
                        );
                    }
                }
            }

            if ($location !== null) {
                $element->setAttribute('url', $this->urlAliasRouter->generate($location));
            }

            // Copy temporary URL if it exists and is not set at this point
            if (!$element->hasAttribute('url') && $element->hasAttribute(EmbedLinking::TEMP_PREFIX . 'url')) {
                $element->setAttribute('url', $element->getAttribute(EmbedLinking::TEMP_PREFIX . 'url'));
            }

            if ($this->elementHasAttribute($element, 'anchor_name')) {
                $element->setAttribute(
                    'url',
                    $element->getAttribute('url') . '#' .
                    $this->getElementAttribute($element, 'anchor_name')
                );
            }
        }
    }

    /**
     * Returns boolean on presence of given $attributeName on a link or embed element.
     *
     * If given $element is embed attribute value will be copied with a prefixed name.
     *
     * @param \DOMElement $element
     * @param string $attributeName
     *
     * @return bool
     */
    protected function elementHasAttribute(DomElement $element, $attributeName)
    {
        // First try to return for link
        if ($element->localName === 'link' && $element->hasAttribute($attributeName)) {
            return true;
        }

        // Second return for embed
        if ($element->hasAttribute(EmbedLinking::TEMP_PREFIX . $attributeName)) {
            return true;
        }

        return false;
    }

    /**
     * Returns value given $attributeName on a link or embed element.
     *
     * If given $element is embed attribute value will be copied with a prefixed name.
     *
     * @param \DOMElement $element
     * @param string $attributeName
     *
     * @return string
     */
    protected function getElementAttribute(DomElement $element, $attributeName)
    {
        // First try to return for link
        if ($element->localName === 'link') {
            return $element->getAttribute($attributeName);
        }

        // Second return for embed
        return $element->getAttribute(EmbedLinking::TEMP_PREFIX . $attributeName);
    }
}
