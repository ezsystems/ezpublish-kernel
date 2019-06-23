<?php

/**
 * This file contains the Aggregate converter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText\Converter;

use eZ\Publish\Core\FieldType\RichText\Converter;
use DOMDocument;

/**
 * Aggregate converter converts using configured converters in prioritized order.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\RichText\Converter\Aggregate from EzPlatformRichTextBundle.
 */
class Aggregate implements Converter
{
    /**
     * An array of converters, sorted by priority.
     *
     * @var \eZ\Publish\Core\FieldType\RichText\Converter[]
     */
    protected $converters = [];

    /**
     * @param \eZ\Publish\Core\FieldType\RichText\Converter[] $converters An array of Converters, sorted by priority
     */
    public function __construct(array $converters = [])
    {
        $this->converters = $converters;
    }

    /**
     * Performs conversion of the given $document using configured converters.
     *
     * @param \DOMDocument $document
     *
     * @return \DOMDocument
     */
    public function convert(DOMDocument $document)
    {
        foreach ($this->converters as $converter) {
            $document = $converter->convert($document);
        }

        return $document;
    }
}
