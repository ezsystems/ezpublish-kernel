<?php
/**
 * File containing the EmbedLinking class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * EmbedLinking converter adds link parameters to the embed element
 * and unwraps the embed from the link if needed.
 */
class EmbedLinking implements Converter
{
    /**
     * Prefix of the link attribute names potentially copied to the embed element.
     *
     * @const string
     */
    const TEMP_PREFIX = "ezlegacytmp-embed-link-";

    public function convert( DOMDocument $document )
    {
        $xpath = new DOMXPath( $document );
        // Select embeds that are linked
        // After Expanding converter such links will contain only single embed element
        $xpathExpression = "//embed[parent::link]|//embed-inline[parent::link]";

        $linkedEmbeds = $xpath->query( $xpathExpression );

        $collection = array();
        foreach ( $linkedEmbeds as $embed )
        {
            $collection[] = $embed;
        }

        /** @var \DOMElement $embed */
        foreach ( $collection as $embed )
        {
            $this->copyLinkAttributes( $embed );
            $this->unwrap( $embed );
        }
    }

    /**
     * Copies embed's link attributes to linked embed itself, prefixed so they can be
     * unambiguously recognized.
     *
     * @param \DOMElement $embed
     */
    protected function copyLinkAttributes( DOMElement $embed )
    {
        $link = $embed->parentNode;

        if ( $link->hasAttribute( "object_id" ) )
        {
            $embed->setAttribute( static::TEMP_PREFIX . "object_id", $link->getAttribute( "object_id" ) );
        }

        if ( $link->hasAttribute( "node_id" ) )
        {
            $embed->setAttribute( static::TEMP_PREFIX . "node_id", $link->getAttribute( "node_id" ) );
        }

        if ( $link->hasAttribute( "anchor_name" ) )
        {
            $embed->setAttribute( static::TEMP_PREFIX . "anchor_name", $link->getAttribute( "anchor_name" ) );
        }

        if ( $link->hasAttribute( "target" ) )
        {
            $embed->setAttribute( static::TEMP_PREFIX . "target", $link->getAttribute( "target" ) );
        }

        if ( $link->hasAttribute( "xhtml:title" ) )
        {
            $embed->setAttribute( static::TEMP_PREFIX . "title", $link->getAttribute( "xhtml:title" ) );
        }

        if ( $link->hasAttribute( "xhtml:id" ) )
        {
            $embed->setAttribute( static::TEMP_PREFIX . "id", $link->getAttribute( "xhtml:id" ) );
        }

        if ( $link->hasAttribute( "class" ) )
        {
            $embed->setAttribute( static::TEMP_PREFIX . "class", $link->getAttribute( "class" ) );
        }

        if ( $link->hasAttribute( "url" ) )
        {
            $embed->setAttribute( static::TEMP_PREFIX . "url", $link->getAttribute( "url" ) );
        }

        if ( $link->hasAttribute( "url_id" ) )
        {
            $embed->setAttribute( static::TEMP_PREFIX . "url_id", $link->getAttribute( "url_id" ) );
        }
    }

    /**
     * Unwraps embed element in the case when it is single content of its link.
     *
     * The above should always be the case for block level embed after Expanding conversion pass.
     * If embed (inline) is not the single content of its link, it won't be unwrapped and link
     * parameters on the embed will signify this with 'wrapping' parameter set to true (done later
     * in EzLinkToHtml5 converter).
     *
     * @param \DOMElement $embed
     */
    protected function unwrap( DOMElement $embed )
    {
        $link = $embed->parentNode;
        $childCount = 0;

        /** @var \DOMText|\DOMElement $node */
        foreach ( $link->childNodes as $node )
        {
            if ( !( $node->nodeType === XML_TEXT_NODE && $node->isWhitespaceInElementContent() ) )
            {
                $childCount += 1;
            }
        }

        if ( $childCount === 1 )
        {
            $link->parentNode->replaceChild( $embed, $link );
        }
    }
}
