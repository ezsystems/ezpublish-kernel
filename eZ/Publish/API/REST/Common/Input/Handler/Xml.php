<?php
/**
 * File containing the Handler base class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Input\Handler;
use eZ\Publish\API\REST\Common\Input\Handler;
use eZ\Publish\API\REST\Common\Exceptions;

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
     * @todo Semantical exceptions for lists
     */
    public function convert( $string )
    {
        $oldXmlErrorHandling = libxml_use_internal_errors( true );
        libxml_clear_errors();

        $dom = new \DOMDocument();
        $dom->loadXml( $string );

        $errors = libxml_get_errors();

        libxml_clear_errors();
        libxml_use_internal_errors( $oldXmlErrorHandling );

        if ( $errors )
        {
            $message = "Detected errors in input XML:\n";
            foreach ( $errors as $error )
            {
                $message .= sprintf(
                    " - In line %d character %d: %s\n",
                    $error->line,
                    $error->column,
                    $error->message
                );
            }
            throw new Exceptions\Parser( $message );
        }

        return $this->convertDom( $dom );
    }

    /**
     * Converts DOM nodes to array structures
     *
     * @param \DOMNode $node
     * @return array
     */
    protected function convertDom( \DOMNode $node )
    {
        $isArray = false;
        $current = array();
        $text    = '';

        if ( $node instanceof \DOMElement )
        {
            foreach ( $node->attributes as $name => $attribute )
            {
                $current["_{$name}"] = $attribute->value;
            }
        }

        foreach ( $node->childNodes as $childNode )
        {
            switch ( $childNode->nodeType )
            {
                case XML_ELEMENT_NODE:
                    $tagName = $childNode->tagName;

                    if ( !isset( $current[$tagName]  ) )
                    {
                        $current[$tagName] = $this->convertDom( $childNode );
                    }
                    elseif ( !$isArray )
                    {
                        $current[$tagName] = array(
                            $current[$tagName],
                            $this->convertDom( $childNode )
                        );
                        $isArray = true;
                    }
                    else
                    {
                        $current[$tagName][] = $this->convertDom( $childNode );
                    }

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
