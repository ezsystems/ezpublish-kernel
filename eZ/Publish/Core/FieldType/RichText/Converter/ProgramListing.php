<?php

/**
 * File containing the ProgramListing class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText\Converter;

use eZ\Publish\Core\FieldType\RichText\Converter;
use DOMDocument;
use DOMXPath;

/**
 * Class ProgramListing.
 *
 * Processes <code>programlisting</code> DocBook tag.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\RichText\Converter\ProgramListing from EzPlatformRichTextBundle.
 */
class ProgramListing implements Converter
{
    /**
     * CDATA's content cannot contain the sequence ']]>' as that will terminate the CDATA section.
     * So, if the end sequence ']]>' appears in the string, we split the text into multiple CDATA sections.
     *
     * @param DOMDocument $document
     * @return DOMDocument
     */
    public function convert(DOMDocument $document)
    {
        $xpath = new DOMXPath($document);
        $xpathExpression = '//ns:pre';
        $ns = $document->documentElement->namespaceURI;
        $xpath->registerNamespace('ns', $ns);
        $elements = $xpath->query($xpathExpression);

        foreach ($elements as $element) {
            $element->textContent = str_replace(']]>', ']]]]><![CDATA[>', $element->textContent);
        }

        return $document;
    }
}
