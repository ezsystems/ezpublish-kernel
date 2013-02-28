<?php
/**
 * File containing the Xml handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Input\Handler;

use eZ\Publish\Core\REST\Common\Input\Handler;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Input format handler base class
 */
class Xml extends Handler
{
    /**
     * Force list for those items
     *
     * The key defines the item in which a list is formed. A list is then
     * formed for every value in the value array.
     *
     * @var array
     */
    protected $forceList = array(
        'ContentList' => array(
            'Content',
        ),
        'ContentTypeList' => array(
            'ContentType',
        ),
        'ContentTypeGroupRefList' => array(
            'ContentTypeGroupRef',
        ),
        'SectionList' => array(
            'Section',
        ),
        'RoleList' => array(
            'Role',
        ),
        'PolicyList' => array(
            'Policy',
        ),
        'LocationList' => array(
            'Location'
        ),
        'ContentObjectStates' => array(
            'ObjectState'
        ),
        'FieldDefinitions' => array(
            'FieldDefinition'
        ),
        'names' => array(
            'value'
        ),
        'descriptions' => array(
            'value'
        ),
        'fields' => array(
            'field'
        ),
        'limitations' => array(
            'limitation'
        ),
        'values' => array(
            'ref'
        )
    );

    protected $fieldTypeHashElements = array(
        'fieldValue',
    );

    /**
     * Converts the given string to an array structure
     *
     * @param string $string
     *
     * @return array
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
            $message .= "\nIn XML: \n\n" . $string;

            throw new Exceptions\Parser( $message );
        }

        return $this->convertDom( $dom );
    }

    /**
     * Converts DOM nodes to array structures
     *
     * @param \DOMNode $node
     *
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

        $parentTagName = $node instanceof \DOMElement ? $node->tagName : false;
        foreach ( $node->childNodes as $childNode )
        {
            switch ( $childNode->nodeType )
            {
                case XML_ELEMENT_NODE:
                    $tagName = $childNode->tagName;

                    if ( in_array( $tagName, $this->fieldTypeHashElements ) )
                    {
                        $current[$tagName] = $this->parseFieldTypeHash( $childNode );
                    }
                    else if ( !isset( $current[$tagName]  ) )
                    {
                        if ( isset( $this->forceList[$parentTagName] ) &&
                             in_array( $tagName, $this->forceList[$parentTagName], true ) )
                        {
                            $isArray = true;
                            $current[$tagName] = array(
                                $this->convertDom( $childNode )
                            );
                        }
                        else
                        {
                            $current[$tagName] = $this->convertDom( $childNode );
                        }
                    }
                    else if ( !$isArray )
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
        else if ( $text !== '' )
        {
            $current = $text;
        }
        else if ( !count( $current ) )
        {
            return null;
        }

        return $current;
    }

    /**
     * @param \DOMElement $domElement
     *
     * @return array|string|null
     */
    protected function parseFieldTypeHash( \DOMElement $domElement )
    {
        $result = $this->parseFieldTypeValues( $domElement->childNodes );

        if ( is_array( $result ) && empty( $result ) )
        {
            // No child values means null
            return null;
        }

        return $result;
    }

    /**
     * Parses a node list of <value> elements
     *
     * @param \DOMNodeList $valueNodes
     *
     * @return array|string
     */
    protected function parseFieldTypeValues( \DOMNodeList $valueNodes )
    {
        $resultValues = array();
        $resultString = '';

        foreach ( $valueNodes as $valueNode )
        {
            switch ( $valueNode->nodeType )
            {
                case XML_ELEMENT_NODE:
                    if ( $valueNode->tagName !== 'value' )
                    {
                        throw new \RuntimeException(
                            sprintf(
                                'Invalid value tag: <%s>.',
                                $valueNode->tagName
                            )
                        );
                    }

                    $parsedValue = $this->parseFieldTypeValues( $valueNode->childNodes );
                    if ( $valueNode->hasAttribute( 'key' ) )
                    {
                        $resultValues[$valueNode->getAttribute( 'key' )] = $parsedValue;
                    }
                    else
                    {
                        $resultValues[] = $parsedValue;
                    }
                    break;

                case XML_TEXT_NODE:
                    $resultString .= $valueNode->wholeText;
                    break;

                case XML_CDATA_SECTION_NODE:
                    $resultString .= $valueNode->data;
                    break;
            }
        }

        $resultString = trim( $resultString );
        if ( $resultString !== '' )
        {
            return $this->castScalarValue( $resultString );
        }
        return $resultValues;
    }

    /**
     * Attempts to cast the given $stringValue into a sensible scalar type
     *
     * @param string $stringValue
     *
     * @return mixed
     */
    protected function castScalarValue( $stringValue )
    {
        switch ( true )
        {
            case ( ctype_digit( $stringValue ) ):
                return (int)$stringValue;

            case ( preg_match( '(^[0-9\.]+$)', $stringValue ) === 1 ):
                return (float)$stringValue;

            case ( strtolower( $stringValue ) === 'true' ):
                return true;

            case ( strtolower( $stringValue ) === 'false' ):
                return false;
        }
        return $stringValue;
    }
}
