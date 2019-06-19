<?php

/**
 * This file contains the ConverterDispatcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use DOMDocument;

/**
 * Dispatcher for various converters depending on the XML document namespace.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\RichText\ConverterDispatcher from EzPlatformRichTextBundle.
 */
class ConverterDispatcher
{
    /**
     * Mapping of namespaces to converters.
     *
     * @var \eZ\Publish\Core\FieldType\RichText\Converter[]
     */
    protected $mapping = [];

    /**
     * @param \eZ\Publish\Core\FieldType\RichText\Converter[] $converterMap
     */
    public function __construct($converterMap)
    {
        foreach ($converterMap as $namespace => $converter) {
            $this->addConverter($namespace, $converter);
        }
    }

    /**
     * Adds converter mapping.
     *
     * @param string $namespace
     * @param null|\eZ\Publish\Core\FieldType\RichText\Converter $converter
     */
    public function addConverter($namespace, Converter $converter = null)
    {
        $this->mapping[$namespace] = $converter;
    }

    /**
     * Dispatches DOMDocument to the namespace mapped converter.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param \DOMDocument $document
     *
     * @return \DOMDocument
     */
    public function dispatch(DOMDocument $document)
    {
        $documentNamespace = $document->documentElement->lookupNamespaceURI(null);
        // checking for null as ezxml has no default namespace...
        if ($documentNamespace === null) {
            $documentNamespace = $document->documentElement->lookupNamespaceURI('xhtml');
        }

        foreach ($this->mapping as $namespace => $converter) {
            if ($documentNamespace === $namespace) {
                if ($converter === null) {
                    return $document;
                }

                return $converter->convert($document);
            }
        }

        throw new NotFoundException('Converter', $documentNamespace);
    }
}
