<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\RichText;

use DOMDocument;
use eZ\Publish\Core\FieldType\RichText\Exception\InvalidXmlException;

final class DOMDocumentFactory
{
    /**
     * Creates \DOMDocument from given $xmlString.
     *
     * @throws \eZ\Publish\Core\FieldType\RichText\Exception\InvalidXmlException
     *
     * @param string $xmlString
     *
     * @return \DOMDocument
     */
    public function loadXMLString(string $xmlString): DOMDocument
    {
        $document = new DOMDocument();

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        // Options:
        // - substitute entities
        // - disable network access
        // - relax parser limits for document size/complexity
        $success = $document->loadXML($xmlString, LIBXML_NOENT | LIBXML_NONET | LIBXML_PARSEHUGE);
        if (!$success) {
            throw new InvalidXmlException('xmlString', libxml_get_errors());
        }

        return $document;
    }
}
