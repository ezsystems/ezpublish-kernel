<?php

/**
 * File containing the eZ\Publish\Core\FieldType\RichText\Converter\Render class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText\Converter;

use eZ\Publish\Core\FieldType\RichText\RendererInterface;
use DOMElement;
use DOMNode;

/**
 * Base class for Render converters.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\RichText\Converter\Render from EzPlatformRichTextBundle.
 */
abstract class Render
{
    /** @var \eZ\Publish\Core\FieldType\RichText\RendererInterface */
    protected $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Extracts configuration hash from embed element.
     *
     * @param \DOMElement $embed
     *
     * @return array
     */
    protected function extractConfiguration(DOMElement $embed)
    {
        $hash = [];
        $configElements = $embed->getElementsByTagName('ezconfig');

        if ($configElements->length) {
            $hash = $this->extractHash($configElements->item(0));
        }

        return $hash;
    }

    /**
     * Recursively extracts data from XML hash structure.
     *
     * @param \DOMNode $configHash
     *
     * @return array
     */
    protected function extractHash(DOMNode $configHash)
    {
        $hash = [];

        foreach ($configHash->childNodes as $node) {
            /** @var \DOMText|\DOMElement $node */
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $hash[$node->getAttribute('key')] = $this->extractHash($node);
            } elseif ($node->nodeType === XML_TEXT_NODE && !$node->isWhitespaceInElementContent()) {
                return $node->wholeText;
            }
        }

        return $hash;
    }
}
