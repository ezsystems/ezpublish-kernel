<?php

/**
 * File containing the eZ\Publish\Core\FieldType\RichText\InternalLinkValidator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText;

use DOMDocument;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Validator for RichText internal format links.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\RichText\Validator\InternalLinkValidator from EzPlatformRichTextBundle.
 */
class InternalLinkValidator
{
    /** @var \eZ\Publish\SPI\Persistence\Content\Handler */
    private $contentHandler;

    /** @var \eZ\Publish\SPI\Persistence\Content\Location\Handler; */
    private $locationHandler;

    /**
     * InternalLinkValidator constructor.
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     */
    public function __construct(ContentHandler $contentHandler, LocationHandler $locationHandler)
    {
        $this->contentHandler = $contentHandler;
        $this->locationHandler = $locationHandler;
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
                    $this->contentHandler->loadContentInfo($id);
                    break;
                case 'ezremote':
                    $this->contentHandler->loadContentInfoByRemoteId($id);
                    break;
                case 'ezlocation':
                    $this->locationHandler->load($id);
                    break;
                default:
                    throw new InvalidArgumentException($scheme, "Given scheme '{$scheme}' is not supported.");
            }
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
