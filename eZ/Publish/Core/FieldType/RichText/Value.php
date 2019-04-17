<?php

/**
 * File containing the RichText Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use DOMDocument;

/**
 * Value for RichText field type.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\FieldType\RichText\Value from EzPlatformRichTextBundle.
 */
class Value extends BaseValue
{
    const EMPTY_VALUE = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0-variant ezpublish-1.0"/>
EOT;

    /**
     * XML content as DOMDocument.
     *
     * @var \DOMDocument
     */
    public $xml;

    /**
     * Initializes a new RichText Value object with $xmlDoc in.
     *
     * @param \DOMDocument|string $xml
     */
    public function __construct($xml = null)
    {
        if ($xml instanceof DOMDocument) {
            $this->xml = $xml;
        } else {
            $this->xml = new DOMDocument();
            $this->xml->loadXML($xml === null ? self::EMPTY_VALUE : $xml);
        }
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return isset($this->xml) ? (string)$this->xml->saveXML() : self::EMPTY_VALUE;
    }
}
