<?php
/**
 * File containing the Handler base class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Handler;
use eZ\Publish\API\REST\Server\Handler;

/**
 * Input format handler base class
 */
class Xml extends Handler
{
    /**
     * Converts the given string to an array structure
     *
     * @param string $string
     * @return array
     * @todo Error handling
     * @todo Semantical exceptions for lists
     */
    public function convert( $string )
    {
        $dom = new DOMDocument();
        $dom->loadXml( $string );

        return $this->convertDom( $dom );
    }

    /**
     * Converts DOM nodes to array structures
     *
     * @param \DOMNode $domElement
     * @return array
     */
    protected function convertDom( \DOMNode $domElement )
    {
        $current = array();
        $text    = '';

        foreach ( $domElement->childNodes as $childNode )
        {
            switch ( $childNode->nodeType )
            {
                case XML_ELEMENT_NODE:
                    $tagName = $childNode->tagName;

                    if ( isset( $current[$tagName] ) && !is_array( $current[$tagName] ) )
                    {
                        $current[$tagName] = array(
                            $current[$tagName],
                            $this->convertDom( $childNode )
                        );
                    }
                    elseif ( !isset( $current[$tagName]  ) )
                    {
                        $current[$tagName] = $this->convertDom( $childNode );
                    }
                    else
                    {
                        $current[$tagName][] = $this->convertDom( $childNode );
                    }
                    break;

                case XML_ATTRIBUTE_NODE:
                    $current["_{$childNode->name}"] = $childNode->value;
                    break;

                case XML_TEXT_NODE:
                    $text .= $childNode->wholeText;
                    break;

                case XML_CDATA_SECTION_NODE:
                    $text .= $childNode->data;
                    break;
            }
        }

        $text = trim( $text );

        if ( $text !== '' && count( $current ) )
        {
            $current["#text"] = $text;
        }
        elseif ( $text !== '' )
        {
            $current = $text;
        }

        return $current;
    }
}
