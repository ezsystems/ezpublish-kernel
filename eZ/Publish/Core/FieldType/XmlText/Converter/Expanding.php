<?php
/**
 * File containing the Expanding class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter;
use DOMDocumentFragment;
use DOMDocument;
use DOMElement;
use DOMXPath;
use DOMNode;

/**
 * Expanding converter expands paragraphs by specific contained elements.
 */
class Expanding implements Converter
{
    /**
     * Attribute denoting inherited tanglement
     *
     * @const string
     */
    const ATTRIBUTE_INHERIT_TANGLEMENT = "ez-inherit-tanglement";

    /**
     * Holds map of the elements that expand the paragraph.
     *
     * Name of the element is first level key, second level keys
     * hold names of the elements that CAN wrap it, and which will be kept
     * as wrappers when paragraph is expanded.
     *
     * @var array
     */
    protected $containmentMap = array(
        "embed" => array(
            "link" => true,
        ),
        "table" => array(),
        "ul" => array(),
        "ol" => array(),
        "literal" => array(),
    );

    public function convert( DOMDocument $document )
    {
        // First mark temporary paragraphs by checking the namespace as an attribute.
        // Reason: as namespace on an element is inherited for all children, it is unusable
        // in XSL transformation.
        /** @var \DOMElement $paragraph */
        foreach ( $document->getElementsByTagName( 'paragraph' ) as $paragraph )
        {
            if ( $paragraph->hasAttribute( 'xmlns:tmp' ) )
            {
                $paragraph->setAttribute( 'ez-temporary', 1 );
            }
        }

        $xpath = new DOMXPath( $document );
        $containedExpression = "//" . implode( "|//", array_keys( $this->containmentMap ) );
        // Select all paragraphs containing elements that need expansion,
        // except temporary paragraphs
        $xpathExpression = "//paragraph[not(@ez-temporary=1) and ($containedExpression)]";

        $paragraphs = $xpath->query( $xpathExpression );

        $paragraphsDepthSorted = array();

        foreach ( $paragraphs as $paragraph )
        {
            $paragraphsDepthSorted[$this->getNodeDepth( $paragraph )][] = $paragraph;
        }

        // Process deepest paragraphs first to avoid conflicts
        krsort( $paragraphsDepthSorted, SORT_NUMERIC );

        foreach ( $paragraphsDepthSorted as $paragraphs )
        {
            foreach ( $paragraphs as $paragraph )
            {
                $this->expandParagraph( $document, $paragraph );
            }
        }
    }

    /**
     * Expands the given $paragraph element, as defined in containment map.
     *
     * @param \DOMDocument $document
     * @param \DOMElement $paragraph
     */
    protected function expandParagraph( DOMDocument $document, DOMElement $paragraph )
    {
        $paragraph->parentNode->replaceChild(
            $this->expandElement( $document, $paragraph ),
            $paragraph
        );
    }

    /**
     * Expands the given $paragraph element, as defined in containment map.
     *
     * Returns document fragment holding expanded elements, which can be used by the
     * caller to replace expanded child.
     *
     * Implemented as a separate method for the benefit of recursion.
     *
     * @param \DOMDocument $document
     * @param \DOMElement $element
     *
     * @return \DOMDocumentFragment
     */
    protected function expandElement( DOMDocument $document, DOMElement $element )
    {
        $fragment = $document->createDocumentFragment();
        $expandingElement = $this->cloneAndEmpty( $element );

        /** @var \DOMElement $node */
        foreach ( $element->childNodes as $node )
        {
            // If node was untangled continue with next one
            // New expanding element will be started by the sub-routine in that case
            if ( $this->isTangled( $node ) )
            {
                $this->untangleNode( $fragment, $element, $expandingElement, $node );
                continue;
            }

            // Expand sub-node if it is element
            if ( $node->nodeType === XML_ELEMENT_NODE )
            {
                $subFragment = $this->expandElement( $document, $node );

                /** @var \DOMElement $subNode */
                foreach ( $subFragment->childNodes as $subNode )
                {
                    // If not untangled just append to existing expanding element, otherwise new
                    // expanding element will be started by the sub-routine
                    if ( $this->isTangled( $subNode ) )
                    {
                        $this->untangleNode( $fragment, $element, $expandingElement, $subNode );
                    }
                    else
                    {
                        $expandingElement->appendChild( $subNode->cloneNode( true ) );
                    }
                }
            }
            // Else just append it to the expanding element
            else
            {
                $expandingElement->appendChild( $node->cloneNode( true ) );
            }
        }

        // Append only if expanded element is not empty, or was empty to begin with
        if ( $element->childNodes->length === 0 || $expandingElement->childNodes->length > 0 )
        {
            $fragment->appendChild( $expandingElement );
        }

        return $fragment;
    }

    /**
     * Untangles given $node from $element, appending expanded elements to the $fragment.
     *
     * Note that $expandingElement is intentionally passed by reference. It can be
     * appended to the $fragment and recreated anew, which needs to picked up by the
     * caller.
     *
     * @param \DOMDocumentFragment $fragment
     * @param \DOMElement $element
     * @param \DOMElement $expandingElement
     * @param \DOMNode $node
     *
     * @return boolean
     */
    protected function untangleNode(
        DOMDocumentFragment $fragment,
        DOMElement $element,
        DOMElement &$expandingElement,
        DOMNode $node
    )
    {
        // Execute if node is entangled in the paragraph context
        if ( $this->isTangled( $node ) )
        {
            // If expanding element is not empty, append it to the fragment and start a new one
            if ( $expandingElement->childNodes->length > 0 )
            {
                $fragment->appendChild( $expandingElement );
                $expandingElement = $this->cloneAndEmpty( $element );
            }

            // If element is the entangler append the node directly to the fragment
            if ( $this->isTangler( $element, $node ) )
            {
                $fragment->appendChild( $node->cloneNode( true ) );
            }
            // Else wrap it in the expanding element and append that to the fragment
            else
            {
                $expandingElement->appendChild( $node->cloneNode( true ) );
                $expandingElement->setAttribute( static::ATTRIBUTE_INHERIT_TANGLEMENT, 1 );
                $fragment->appendChild( $expandingElement );

                // Start new expanding element
                $expandingElement = $this->cloneAndEmpty( $element );
            }

            return true;
        }

        return false;
    }

    /**
     * Returns boolean depending if given $node is entangled or not.
     *
     * @param \DOMNode $node
     *
     * @return boolean
     */
    protected function isTangled( DOMNode $node )
    {
        return (
            isset( $this->containmentMap[$node->localName] )
            || ( $node instanceof DOMElement && $node->hasAttribute( static::ATTRIBUTE_INHERIT_TANGLEMENT ) )
        );
    }

    /**
     * Returns boolean depending if given $element is entangler of $node or not.
     *
     * @param \DOMElement $element
     * @param \DOMNode $node
     *
     * @return boolean
     */
    protected function isTangler( DOMElement $element, DOMNode $node )
    {
        return (
            !isset( $this->containmentMap[$node->localName][$element->localName] )
            || ( $node instanceof DOMElement && $node->hasAttribute( static::ATTRIBUTE_INHERIT_TANGLEMENT ) )
        );
    }

    /**
     * Clones given $element and removes all children from clone.
     *
     * @param \DOMElement $element
     *
     * @return \DOMElement
     */
    protected function cloneAndEmpty( DOMElement $element )
    {
        $clone = $element->cloneNode( true );

        $children = array();

        // Collect child nodes first, as we can't iterate and
        // remove from \DOMNodeList directly
        foreach ( $clone->childNodes as $node )
        {
            $children[] = $node;
        }

        foreach ( $children as $node )
        {
            $clone->removeChild( $node );
        }

        return $clone;
    }

    /**
     * Returns depth of given $node in a DOMDocument.
     *
     * @param \DOMNode $node
     *
     * @return int
     */
    protected function getNodeDepth( DomNode $node )
    {
        $depth = -2;

        while ( $node )
        {
            $depth++;
            $node = $node->parentNode;
        }

        return $depth;
    }
}
