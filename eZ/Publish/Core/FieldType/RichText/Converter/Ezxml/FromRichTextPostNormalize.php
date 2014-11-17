<?php
/**
 * File containing the eZ\Publish\Core\FieldType\RichText\Converter\Ezxml\FromRichTextPostNormalize class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RichText\Converter\Ezxml;

use eZ\Publish\Core\FieldType\RichText\Converter;
use DOMDocument;
use DOMXPath;

/**
 * Expands paragraphs and links embeds of a XML document in legacy ezxml format.
 */
class FromRichTextPostNormalize implements Converter
{
    /**
     * Converts given $document into another \DOMDocument object
     *
     * @param \DOMDocument $document
     *
     * @return \DOMDocument
     */
    public function convert( DOMDocument $document )
    {
        $xpath = new DOMXPath( $document );
        $temporaryParagraphs = $xpath->query( "//paragraph[@ez-temporary='1']" );

        /** @var \DOMElement $paragraph */
        foreach ( $temporaryParagraphs as $paragraph )
        {
            $paragraph->removeAttribute( "ez-temporary" );
            $paragraph->setAttribute( "xmlns:tmp", "http://ez.no/namespaces/ezpublish3/temporary/" );
        }

        return $document;
    }
}
