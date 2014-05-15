<?php
/**
 * File containing the CustomTags class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use DOMDocument;
use eZ\Publish\Core\FieldType\XmlText\Converter;

/**
 * Pre-converter for custom tags, used to determine if it's supposed to be inline or not.
 * "block" custom tags are always contained in a <paragraph> declaring a local "tmp" namespace.
 */
class CustomTags implements Converter
{
    public function convert( DOMDocument $xmlDoc )
    {
        /** @var \DOMElement $customTag */
        foreach ( $xmlDoc->getElementsByTagName( 'custom' ) as $customTag )
        {
            if ( $customTag->parentNode->lookupNamespaceUri( 'tmp' ) !== null )
            {
                $customTag->setAttribute( 'inline', 'false' );
            }
        }
    }
}
