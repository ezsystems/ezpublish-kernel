<?php

/**
 * File containing the RichTextStorage class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\RichText;

use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Psr\Log\LoggerInterface;
use DOMDocument;
use DOMXPath;

class RichTextStorage extends GatewayBasedStorage
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \eZ\Publish\Core\FieldType\StorageGateway[] $gateways
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(array $gateways = array(), LoggerInterface $logger = null)
    {
        parent::__construct($gateways);
        $this->logger = $logger;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        /** @var \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway $gateway */
        $gateway = $this->getGateway($context);
        $document = new DOMDocument();
        $document->loadXML($field->value->data);

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('docbook', 'http://docbook.org/ns/docbook');
        // This will select only links with non-empty 'xlink:href' attribute value
        $xpathExpression = "//docbook:link[string( @xlink:href ) and not( starts-with( @xlink:href, 'ezurl://' )" .
            "or starts-with( @xlink:href, 'ezcontent://' )" .
            "or starts-with( @xlink:href, 'ezlocation://' )" .
            "or starts-with( @xlink:href, '#' ) )]";

        $links = $xpath->query($xpathExpression);

        if (empty($links)) {
            return false;
        }

        $urlSet = array();
        $remoteIdSet = array();
        $linksInfo = array();

        /** @var \DOMElement $link */
        foreach ($links as $index => $link) {
            preg_match(
                '~^(ezremote://)?([^#]*)?(#.*|\\s*)?$~',
                $link->getAttribute('xlink:href'),
                $matches
            );
            $linksInfo[$index] = $matches;

            if (empty($matches[1])) {
                $urlSet[$matches[2]] = true;
            } else {
                $remoteIdSet[$matches[2]] = true;
            }
        }

        $urlIdMap = $gateway->getUrlIdMap(array_keys($urlSet));
        $contentIds = $gateway->getContentIds(array_keys($remoteIdSet));
        $urlLinkSet = array();

        foreach ($links as $index => $link) {
            list(, $scheme, $url, $fragment) = $linksInfo[$index];

            if (empty($scheme)) {
                // Insert the same URL only once
                if (!isset($urlIdMap[$url])) {
                    $urlIdMap[$url] = $gateway->insertUrl($url);
                }
                // Link the same URL only once
                if (!isset($urlLinkSet[$url])) {
                    $gateway->linkUrl(
                        $urlIdMap[$url],
                        $field->id,
                        $versionInfo->versionNo
                    );
                    $urlLinkSet[$url] = true;
                }
                $href = "ezurl://{$urlIdMap[$url]}{$fragment}";
            } else {
                if (!isset($contentIds[$url])) {
                    throw new NotFoundException('Content', $url);
                }
                $href = "ezcontent://{$contentIds[$url]}{$fragment}";
            }

            $link->setAttribute('xlink:href', $href);
        }

        $field->value->data = $document->saveXML();

        return true;
    }

    /**
     * Modifies $field if needed, using external data (like for Urls).
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        /** @var \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway $gateway */
        $gateway = $this->getGateway($context);
        $document = new DOMDocument();
        $document->loadXML($field->value->data);

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('docbook', 'http://docbook.org/ns/docbook');
        $xpathExpression = "//docbook:link[starts-with( @xlink:href, 'ezurl://' )]|//docbook:ezlink[starts-with( @xlink:href, 'ezurl://' )]";

        $links = $xpath->query($xpathExpression);

        if (empty($links)) {
            return;
        }

        $urlIdSet = array();
        $urlInfo = array();

        /** @var \DOMElement $link */
        foreach ($links as $index => $link) {
            preg_match(
                '~^ezurl://([^#]*)?(#.*|\\s*)?$~',
                $link->getAttribute('xlink:href'),
                $matches
            );
            $urlInfo[$index] = $matches;

            if (!empty($matches[1])) {
                $urlIdSet[$matches[1]] = true;
            }
        }

        $idUrlMap = $gateway->getIdUrlMap(array_keys($urlIdSet));

        foreach ($links as $index => $link) {
            list(, $urlId, $fragment) = $urlInfo[$index];

            if (isset($idUrlMap[$urlId])) {
                $href = $idUrlMap[$urlId] . $fragment;
            } else {
                // URL id is empty or not in the DB
                if (isset($this->logger)) {
                    $this->logger->error("URL with ID {$urlId} not found");
                }
                $href = '#';
            }

            $link->setAttribute('xlink:href', $href);
        }

        $field->value->data = $document->saveXML();
    }

    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context)
    {
        /** @var \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway $gateway */
        $gateway = $this->getGateway($context);

        foreach ($fieldIds as $fieldId) {
            $gateway->unlinkUrl($fieldId, $versionInfo->versionNo);
        }
    }

    /**
     * Checks if field type has external data to deal with.
     *
     * @return bool
     */
    public function hasFieldData()
    {
        return true;
    }

    public function getIndexData(VersionInfo $versionInfo, Field $field, array $context)
    {
    }
}
