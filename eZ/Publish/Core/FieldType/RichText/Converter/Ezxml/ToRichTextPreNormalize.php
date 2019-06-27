<?php

/**
 * File containing the eZ\Publish\Core\FieldType\RichText\Converter\Ezxml\ToRichTextPreNormalize class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText\Converter\Ezxml;

use eZ\Publish\Core\FieldType\XmlText\Converter\EmbedLinking;
use eZ\Publish\Core\FieldType\XmlText\Converter\Expanding;
use eZ\Publish\Core\FieldType\RichText\Converter;
use DOMDocument;

/**
 * Expands paragraphs and links embeds of a XML document in legacy ezxml format.
 *
 * Relies on XmlText's Expanding and EmbedLinking converters implementation.
 *
 * @deprecated since version 7.2, to be removed in 8.0. Use eZ\Publish\Core\FieldType\XmlText\Converter\ToRichTextPreNormalize instead. *
 */
class ToRichTextPreNormalize implements Converter
{
    /** @var \eZ\Publish\Core\FieldType\XmlText\Converter\Expanding */
    protected $expandingConverter;

    /** @var \eZ\Publish\Core\FieldType\XmlText\Converter\EmbedLinking */
    protected $embedLinkingConverter;

    /**
     * Construct from XmlText converters implementation.
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Converter\Expanding $expandingConverter
     * @param \eZ\Publish\Core\FieldType\XmlText\Converter\EmbedLinking $embedLinkingConverter
     */
    public function __construct(Expanding $expandingConverter, EmbedLinking $embedLinkingConverter)
    {
        $this->expandingConverter = $expandingConverter;
        $this->embedLinkingConverter = $embedLinkingConverter;
    }

    /**
     * Converts given $document into another \DOMDocument object.
     *
     * @param \DOMDocument $document
     *
     * @return \DOMDocument
     */
    public function convert(DOMDocument $document)
    {
        // First
        $this->expandingConverter->convert($document);
        // Second
        $this->embedLinkingConverter->convert($document);

        return $document;
    }
}
