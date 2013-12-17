<?php
/**
 * File containing the XmlText Value class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use DOMDocument;

/**
 * Basic for TextLine field type
 */
class Value extends BaseValue
{
    const EMPTY_VALUE = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0-variant ezpublish-1.0"/>
EOT;

    /**
     * XML content as DOMDocument
     *
     * @var \DOMDocument
     */
    public $xml;

    /**
     * Initializes a new XmlText Value object with $xmlDoc in
     *
     * @param \DOMDocument $xmlDoc
     */
    public function __construct( DOMDocument $xmlDoc = null )
    {
        if ( $xmlDoc === null )
        {
            $xmlDoc = new DOMDocument;
            $xmlDoc->loadXML( self::EMPTY_VALUE );
        }

        $this->xml = $xmlDoc;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return isset( $this->xml ) ? (string)$this->xml->saveXML() : self::EMPTY_VALUE;
    }
}
