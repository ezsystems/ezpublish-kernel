<?php

/**
 * File containing the eZ\Publish\Core\FieldType\RichText\Converter\Link class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText\Converter;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\FieldType\RichText\Converter;
use eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Psr\Log\LoggerInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException as APIUnauthorizedException;
use DOMDocument;
use DOMXPath;

/**
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\RichText\Converter\Link from EzPlatformRichTextBundle.
 */
class Link implements Converter
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    protected $locationService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    protected $contentService;

    /** @var \eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter */
    protected $urlAliasRouter;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    public function __construct(LocationService $locationService, ContentService $contentService, UrlAliasRouter $urlAliasRouter, LoggerInterface $logger = null)
    {
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->urlAliasRouter = $urlAliasRouter;
        $this->logger = $logger;
    }

    /**
     * Converts internal links (ezcontent:// and ezlocation://) to URLs.
     *
     * @param \DOMDocument $document
     *
     * @return \DOMDocument
     */
    public function convert(DOMDocument $document)
    {
        $document = clone $document;
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('docbook', 'http://docbook.org/ns/docbook');
        $linkAttributeExpression = "starts-with( @xlink:href, 'ezlocation://' ) or starts-with( @xlink:href, 'ezcontent://' )";
        $xpathExpression = "//docbook:link[{$linkAttributeExpression}]|//docbook:ezlink";

        /** @var \DOMElement $link */
        foreach ($xpath->query($xpathExpression) as $link) {
            // Set resolved href to number character as a default if it can't be resolved
            $hrefResolved = '#';
            $href = $link->getAttribute('xlink:href');
            $location = null;
            preg_match('~^(.+://)?([^#]*)?(#.*|\\s*)?$~', $href, $matches);
            list(, $scheme, $id, $fragment) = $matches;

            if ($scheme === 'ezcontent://') {
                try {
                    $contentInfo = $this->contentService->loadContentInfo($id);
                    $location = $this->locationService->loadLocation($contentInfo->mainLocationId);
                    $hrefResolved = $this->urlAliasRouter->generate($location) . $fragment;
                } catch (APINotFoundException $e) {
                    if ($this->logger) {
                        $this->logger->warning(
                            'While generating links for richtext, could not locate ' .
                            'Content object with ID ' . $id
                        );
                    }
                } catch (APIUnauthorizedException $e) {
                    if ($this->logger) {
                        $this->logger->notice(
                            'While generating links for richtext, unauthorized to load ' .
                            'Content object with ID ' . $id
                        );
                    }
                }
            } elseif ($scheme === 'ezlocation://') {
                try {
                    $location = $this->locationService->loadLocation($id);
                    $hrefResolved = $this->urlAliasRouter->generate($location) . $fragment;
                } catch (APINotFoundException $e) {
                    if ($this->logger) {
                        $this->logger->warning(
                            'While generating links for richtext, could not locate ' .
                            'Location with ID ' . $id
                        );
                    }
                } catch (APIUnauthorizedException $e) {
                    if ($this->logger) {
                        $this->logger->notice(
                            'While generating links for richtext, unauthorized to load ' .
                            'Location with ID ' . $id
                        );
                    }
                }
            } else {
                $hrefResolved = $href;
            }

            $hrefAttributeName = 'xlink:href';

            // For embeds set the resolved href to the separate attribute
            // Original href needs to be preserved in order to generate link parameters
            // This will need to change with introduction of UrlService and removal of URL link
            // resolving in external storage
            if ($link->localName === 'ezlink') {
                $hrefAttributeName = 'href_resolved';
            }

            $link->setAttribute($hrefAttributeName, $hrefResolved);
        }

        return $document;
    }
}
