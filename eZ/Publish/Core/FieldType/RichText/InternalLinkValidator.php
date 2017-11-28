<?php

/**
 * File containing the eZ\Publish\Core\FieldType\RichText\InternalLinkValidator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText;

use DOMDocument;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;

/**
 * Validator for RichText internal format links.
 */
class InternalLinkValidator
{
    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     */
    public function __construct(ContentService $contentService, LocationService $locationService)
    {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
    }

    /**
     * Extracts and validate internal links.
     *
     * @param \DOMDocument $xml
     * @return array
     */
    public function validateDocument(DOMDocument $xml)
    {
        $errors = [];

        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('docbook', 'http://docbook.org/ns/docbook');

        foreach (['link', 'ezlink'] as $tagName) {
            $xpathExpression = $this->getXPathForLinkTag($tagName);
            /** @var \DOMElement $element */
            foreach ($xpath->query($xpathExpression) as $element) {
                $url = $element->getAttribute('xlink:href');
                preg_match('~^(.+)://([^#]*)?(#.*|\\s*)?$~', $url, $matches);
                list(, $scheme, $id) = $matches;

                if (empty($id)) {
                    continue;
                }

                if (!$this->validate($scheme, $id)) {
                    $errors[] = $this->getInvalidLinkError($scheme, $url);
                }
            }
        }

        return $errors;
    }

    /**
     * Validates following link formats: 'ezcontent://<contentId>', 'ezremote://<contentRemoteId>', 'ezlocation://<locationId>'.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If given $scheme is not supported.
     *
     * @param string $scheme
     * @param string $id
     *
     * @return bool
     */
    public function validate($scheme, $id)
    {
        try {
            switch ($scheme) {
                case 'ezcontent':
                    $this->contentService->loadContentInfo($id);
                    break;
                case 'ezremote':
                    $this->contentService->loadContentByRemoteId($id);
                    break;
                case 'ezlocation':
                    $this->locationService->loadLocation($id);
                    break;
                default:
                    throw new InvalidArgumentException($scheme, "Given scheme '{$scheme}' is not supported.");
            }
        } catch (UnauthorizedException $e) {
            // Editor can link to content/location even if he doesn’t have permissions
            return true;
        } catch (NotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * Builds error message for invalid url.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If given $scheme is not supported.
     *
     * @param string $scheme
     * @param string $url
     * @return string
     */
    private function getInvalidLinkError($scheme, $url)
    {
        switch ($scheme) {
            case 'ezcontent':
            case 'ezremote':
                return sprintf('Invalid link "%s": target content cannot be found', $url);
            case 'ezlocation':
                return sprintf('Invalid link "%s": target location cannot be found', $url);
            default:
                throw new InvalidArgumentException($scheme, "Given scheme '{$scheme}' is not supported.");
        }
    }

    /**
     * Generates XPath expression for given link tag.
     *
     * @param string $tagName
     * @return string
     */
    private function getXPathForLinkTag($tagName)
    {
        return "//docbook:{$tagName}[starts-with(@xlink:href, 'ezcontent://') or starts-with(@xlink:href, 'ezlocation://') or starts-with(@xlink:href, 'ezremote://')]";
    }
}
