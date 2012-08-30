<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Input\Parser\Simplified class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Input\Parser;

use eZ\Publish\Core\FieldType\XmlText\Input\Parser as InputParser,
    eZ\Publish\Core\FieldType\XmlText\Input\Parser\Base as BaseParser,
    DOMElement;

/**
 * Simplified (native) XmlText input parser
 */
class Simplified extends BaseParser implements InputParser
{
    protected $inputTags = array(
        'b' => array( 'name' => 'strong' ),
        'bold' => array( 'name' => 'strong' ),
        'i' => array( 'name' => 'emphasize' ),
        'em' => array( 'name' => 'emphasize' ),
        'h' => array( 'name' => 'header' ),
        'p' => array( 'name' => 'paragraph' ),
        'para' => array( 'name' => 'paragraph' ),
        'br' => array( 'name' => 'br', 'noChildren' => true ),
        'a' => array( 'name' => 'link' ),
        'h1' => array( 'nameHandler' => 'tagNameHeader' ),
        'h2' => array( 'nameHandler' => 'tagNameHeader' ),
        'h3' => array( 'nameHandler' => 'tagNameHeader' ),
        'h4' => array( 'nameHandler' => 'tagNameHeader' ),
        'h5' => array( 'nameHandler' => 'tagNameHeader' ),
        'h6' => array( 'nameHandler' => 'tagNameHeader' ),
    );

    protected $outputTags = array(
        'section' => array(),
        'embed' => array(
            //'parsingHandler' => 'breakInlineFlow',
            'structHandler' => 'appendLineParagraph',
            'publishHandler' => 'publishHandlerEmbed',
            'attributes' => array( 'id' => 'xhtml:id' ),
            'requiredInputAttributes' => array( 'href' ),
        ),
        'embed-inline' => array( //'parsingHandler' => 'breakInlineFlow',
            'structHandler' => 'appendLineParagraph',
            'publishHandler' => 'publishHandlerEmbed',
            'attributes' => array( 'id' => 'xhtml:id' ),
            'requiredInputAttributes' => array( 'href' ),
        ),
        'object' => array(
            //'parsingHandler' => 'breakInlineFlow',
            'structHandler' => 'appendLineParagraph',
            'publishHandler' => 'publishHandlerObject',
            'attributes' => array(
                'href' => 'image:ezurl_href',
                'target' => 'image:ezurl_target',
                'ezurl_href' => 'image:ezurl_href',
                'ezurl_id' => 'image:ezurl_id',
                'ezurl_target' => 'image:ezurl_target',
            ),
            'requiredInputAttributes' => array( 'id' ),
        ),
        'table' => array( 'structHandler' => 'appendParagraph' ),
        'tr' => array(),
        'td' => array(
            'attributes' => array(
                'width' => 'xhtml:width',
                'colspan' => 'xhtml:colspan',
                'rowspan' => 'xhtml:rowspan',
            ),
        ),
        'th' => array(
            'attributes' => array(
                'width' => 'xhtml:width',
                'colspan' => 'xhtml:colspan',
                'rowspan' => 'xhtml:rowspan',
            ),
        ),
        'ol' => array( 'structHandler' => 'structHandlerLists' ),
        'ul' => array( 'structHandler' => 'structHandlerLists' ),
        'li' => array( 'autoCloseOn' => array( 'li' ) ),
        'header' => array(
            'autoCloseOn' => array( 'paragraph' ),
            'structHandler' => 'structHandlerHeader',
        ),
        'paragraph' => array(
            'autoCloseOn' => array( 'paragraph' ),
            'publishHandler' => 'publishHandlerParagraph',
        ),
        'line' => array(),
        'br' => array(
            'parsingHandler' => 'breakInlineFlow',
            'structHandler' => 'structHandlerBr',
            'attributes' => false,
        ),
        'literal' => array(
            'parsingHandler' => 'parsingHandlerLiteral',
            'structHandler' => 'appendParagraph',
        ),
        'strong' => array( 'structHandler' => 'appendLineParagraph' ),
        'emphasize' => array( 'structHandler' => 'appendLineParagraph' ),
        'link' => array(
            'structHandler' => 'appendLineParagraph',
            'publishHandler' => 'publishHandlerLink',
            'attributes' => array(
                'title' => 'xhtml:title',
                'id' => 'xhtml:id',
            ),
            'requiredInputAttributes' => array( 'href' )
        ),
        'anchor' => array( 'structHandler' => 'appendLineParagraph' ),
        'custom' => array(
            'structHandler' => 'structHandlerCustom',
            'publishHandler' => 'publishHandlerCustom',
            'requiredInputAttributes' => array( 'name' ),
        ),
        '#text' => array( 'structHandler' => 'structHandlerText' ),
    );

    public function process( $xmlString, $createRootNode = true  )
    {
        return parent::process( $xmlString, $createRootNode );
    }

    /**
     * Tag Name handlers (init handlers)
     */
    protected function tagNameHeader( $tagName, &$attributes )
    {
        switch ( $tagName )
        {
            case 'h1':
                $attributes['level'] = '1';
                break;
            case 'h2':
                $attributes['level'] = '2';
                break;
            case 'h3':
                $attributes['level'] = '3';
                break;
            case 'h4':
                $attributes['level'] = '4';
                break;
            case 'h5':
                $attributes['level'] = '5';
                break;
            case 'h6':
                $attributes['level'] = '6';
                break;
            default :
                return '';
        }
        return 'header';
    }

    /**
     * Parsing Handlers (called at pass 1)
     */
    protected function parsingHandlerLiteral( $element, &$param )
    {
        $ret = null;
        $data = $param[0];
        $pos =& $param[1];

        $tablePos = strpos( $data, '</literal>', $pos );
        if ( $tablePos === false )
        {
            $tablePos = strpos( $data, '</LITERAL>', $pos );
        }

        if ( $tablePos === false )
        {
            return $ret;
        }

        $element->appendChild( $this->document->createTextNode( substr( $data, $pos, $tablePos - $pos ) ) );

        $pos = $tablePos + strlen( '</literal>' );

        return false;
    }

    protected function breakInlineFlow( $element, $param )
    {
        // Breaks the flow of inline tags. Used for non-inline tags caught within inline.
        // Works for tags with no children only.
        $data =& $param[0];
        $pos =& $param[1];
        $tagBeginPos = $param[2];
        $parent = $element->parentNode;

        $wholeTagString = substr( $data, $tagBeginPos, $pos - $tagBeginPos );

        if ( $parent && $this->xmlSchema->isInline( $parent ) )
        {
            $insertData = '';
            $currentParent = $parent;
            // Close all parent tags
            end( $this->parentStack );
            do
            {
                $stackData = current( $this->parentStack );
                $currentParentName = $stackData[0];
                $insertData .= "</$currentParentName>";
                $currentParent->setAttributeNS( 'http://ez.no/namespaces/ezpublish3/temporary/', 'tmp:new-element', 'true' );
                $currentParent = $currentParent->parentNode;
                prev( $this->parentStack );
            }
            while ( $this->xmlSchema->isInline( $currentParent ) );

            $insertData .= $wholeTagString;

            $currentParent = $parent;
            end( $this->parentStack );
            $appendData = '';
            do
            {
                $stackData = current( $this->parentStack );
                $currentParentName = $stackData[0];
                $currentParentAttrString = '';
                if ( $stackData[2] )
                {
                    $currentParentAttrString = ' ' . $stackData[2];
                }
                $currentParentAttrString .= " tmp:new-element='true'";
                $appendData = "<$currentParentName$currentParentAttrString>" . $appendData;
                $currentParent = $currentParent->parentNode;
                prev( $this->parentStack );
            }
            while ( $this->xmlSchema->isInline( $currentParent ) );

            $insertData .= $appendData;

            $data = $insertData . substr( $data, $pos );
            $pos = 0;
            $element = $parent->removeChild( $element );
            return false;
        }

        return null;
    }

    /**
     * Structure handlers. (called at pass 2)
     */
    // Structure handler for inline nodes.
    protected function appendLineParagraph( $element, $newParent )
    {
        // eZDebugSetting::writeDebug( 'kernel-datatype-ezxmltext', $newParent, 'eZSimplifiedXMLInputParser::appendLineParagraph new parent' );
        $ret = array();
        $parent = $element->parentNode;
        if ( !$parent instanceof DOMElement )
        {
            return $ret;
        }

        $parentName = $parent->nodeName;
        $newParentName = $newParent != null ? $newParent->nodeName : '';

        // Correct structure by adding <line> and <paragraph> tags.
        if ( $parentName == 'line' || $this->xmlSchema->isInline( $parent ) )
        {
            return $ret;
        }

        if ( $newParentName == 'line' )
        {
            $element = $parent->removeChild( $element );
            $newParent->appendChild( $element );
            $newLine = $newParent;
            $ret['result'] = $newParent;
        }
        elseif ( $parentName == 'paragraph' )
        {
            $newLine = $this->createAndPublishElement( 'line', $ret );
            $element = $parent->replaceChild( $newLine, $element );
            $newLine->appendChild( $element );
            $ret['result'] = $newLine;
        }
        elseif ( $newParentName == 'paragraph' )
        {
            $newLine = $this->createAndPublishElement( 'line', $ret );
            $element = $parent->removeChild( $element );
            $newParent->appendChild( $newLine );
            $newLine->appendChild( $element );
            $ret['result'] = $newLine;
        }
        elseif ( $this->xmlSchema->check( $parent, 'paragraph' ) )
        {
            $newLine = $this->createAndPublishElement( 'line', $ret );
            $newPara = $this->createAndPublishElement( 'paragraph', $ret );
            $element = $parent->replaceChild( $newPara, $element );
            $newPara->appendChild( $newLine );
            $newLine->appendChild( $element );
            $ret['result'] = $newLine;
        }

        return $ret;
    }

    // Structure handler for temporary <br> elements
    protected function structHandlerBr( $element, $newParent )
    {
        $ret = array( 'result' => $newParent );
        $parent = $element->parentNode;

        $next = $element->nextSibling;

        if ( $element->getAttribute( 'ignore' ) != 'true' &&
            $next &&
            $next->nodeName == 'br' )
        {
            if ( $this->xmlSchema->check( $parent, 'paragraph' ) )
            {
                if ( !$newParent )
                {
                    // create paragraph in case of the first empty paragraph
                    $newPara = $this->createAndPublishElement( 'paragraph', $ret );
                    $parent->replaceChild( $newPara, $element );
                }
                elseif ( $newParent->nodeName == 'paragraph' ||
                    $newParent->nodeName == 'line' )
                {
                    // break paragraph or line flow
                    unset( $ret );
                    $ret = array();

                    // Do not process next <br> tag
                    $next->setAttribute( 'ignore', 'true' );

                    // create paragraph in case of the last empty paragraph (not inside section)
                    $nextToNext = $next->nextSibling;
                    $tmp = $parent;
                    while ( !$nextToNext && $tmp && $tmp->nodeName == 'section' )
                    {
                        $nextToNext = $tmp->nextSibling;
                        $tmp = $tmp->parentNode;
                    }
                    if ( !$nextToNext )
                    {
                        $newPara = $this->createAndPublishElement( 'paragraph', $ret );
                        $parent->replaceChild( $newPara, $element );
                    }
                }
            }
        }
        else
        {
            if ( $newParent && $newParent->nodeName == 'line' )
            {
                $ret['result'] = $newParent->parentNode;
            }
        }

        // Trim spaces used for tag indenting
        if ( $next && $next->nodeType == XML_TEXT_NODE && !trim( $next->textContent ) )
        {
            $nextToNext = $next->nextSibling;
            if ( !$nextToNext || $nextToNext->nodeName != 'br' )
            {
                $next = $parent->removeChild( $next );
            }
        }
        return $ret;
    }

    // Structure handler for in-paragraph nodes.
    protected function appendParagraph( $element, &$newParent )
    {
        $ret = array();
        $parent = $element->parentNode;
        if ( !$parent )
        {
            return $ret;
        }

        $parentName = $parent->nodeName;

        if ( $parentName != 'paragraph' )
        {
            if ( $newParent && $newParent->nodeName == 'paragraph' )
            {
                $element = $parent->removeChild( $element );
                $newParent->appendChild( $element );
                $ret['result'] = $newParent;
            }
            elseif ( $newParent && $newParent->parentNode && $newParent->parentNode->nodeName == 'paragraph' )
            {
                $para = $newParent->parentNode;
                $element = $parent->removeChild( $element );
                $para->appendChild( $element );
                $ret['result'] = $newParent->parentNode;
            }
            elseif ( $this->xmlSchema->check( $parentName, 'paragraph' ) )
            {
                $newPara = $this->createAndPublishElement( 'paragraph', $ret );
                $parent->replaceChild( $newPara, $element );
                $newPara->appendChild( $element );
                $ret['result'] = $newPara;
            }
        }
        return $ret;
    }

    // Structure handler for 'header' tag.
    protected function structHandlerHeader( $element, &$param )
    {
        $ret = null;
        $parent = $element->parentNode;
        $level = $element->getAttribute( 'level' );
        if ( $level < 1 )
        {
            $level = 1;
        }

        $element->removeAttribute( 'level' );
        if ( $level )
        {
            $sectionLevel = -1;
            $current = $element;
            while ( $current->parentNode )
            {
                $current = $current->parentNode;
                if ( $current->nodeName == 'section' )
                {
                    $sectionLevel++;
                }
                else
                {
                    if ( $current->nodeName == 'td' )
                    {
                        $sectionLevel++;
                        break;
                    }
                }
            }
            if ( $level > $sectionLevel )
            {
                if ( $this->getOption( self::OPT_STRICT_HEADERS ) &&
                    $level - $sectionLevel > 1 )
                {
                    $this->handleError( BaseParser::ERROR_SCHEMA, "Incorrect headers nesting" );
                }

                $newParent = $parent;
                for ( $i = $sectionLevel; $i < $level; $i++ )
                {
                    $newSection = $this->document->createElement( 'section' );
                    if ( $i == $sectionLevel )
                    {
                        $newSection = $newParent->insertBefore( $newSection, $element );
                    }
                    else
                    {
                        $newParent->appendChild( $newSection );
                    }

                    $newParent = $newSection;
                    unset( $newSection );
                }
                $elementToMove = $element;
                while ( $elementToMove &&
                    $elementToMove->nodeName != 'section' )
                {
                    $next = $elementToMove->nextSibling;
                    $elementToMove = $parent->removeChild( $elementToMove );
                    $newParent->appendChild( $elementToMove );
                    $elementToMove = $next;

                    if ( $elementToMove && $elementToMove->nodeName == 'header' )
                    {
                        // in the case of non-strict headers
                        $headerLevel = $elementToMove->getAttribute( 'level' );
                        if ( $level - $sectionLevel > 1 )
                        {
                            if ( $headerLevel == $level )
                            {
                                $newParent2 = $this->document->createElement( 'section' );
                                $newParent->parentNode->appendChild( $newParent2 );
                                $newParent = $newParent2;
                            }
                            elseif ( $headerLevel < $level )
                            {
                                break;
                            }
                        }
                        else
                        {
                            if ( $headerLevel <= $level )
                            {
                                break;
                            }
                        }
                    }
                }
            }
            elseif ( $level < $sectionLevel )
            {
                $newLevel = $sectionLevel + 1;
                $current = $element;
                while ( $level < $newLevel )
                {
                    $current = $current->parentNode;
                    if ( $current->nodeName == 'section' )
                    {
                        $newLevel--;
                    }
                }
                $elementToMove = $element;
                while ( $elementToMove &&
                    $elementToMove->nodeName != 'section' )
                {
                    $next = $elementToMove->nextSibling;
                    $elementToMove = $parent->removeChild( $elementToMove );
                    $current->appendChild( $elementToMove );
                    $elementToMove = $next;

                    if ( $elementToMove->nodeName == 'header' &&
                        $elementToMove->getAttribute( 'level' ) <= $level )
                    {
                        break;
                    }
                }
            }
        }
        return $ret;
    }

    // Structure handler for 'custom' tag.
    protected function structHandlerCustom( $element, &$params )
    {
        $ret = null;
        if ( $this->xmlSchema->isInline( $element ) )
        {
            $ret = $this->appendLineParagraph( $element, $params );
        }
        else
        {
            $ret = $this->appendParagraph( $element, $params );
        }
        return $ret;
    }

    // Structure handler for 'ul' and 'ol' tags.
    protected function structHandlerLists( $element, &$params )
    {
        $ret = array();
        $parent = $element->parentNode;
        $parentName = $parent->nodeName;

        if ( $parentName == 'paragraph' )
        {
            return $ret;
        }

        // If we are inside a list
        if ( $parentName == 'ol' || $parentName == 'ul' )
        {
            // If previous 'li' doesn't exist, create it,
            // else append to the previous 'li' element.
            $prev = $element->previousSibling;
            if ( !$prev )
            {
                $li = $this->document->createElement( 'li' );
                $li = $parent->insertBefore( $li, $element );
                $element = $parent->removeChild( $element );
                $li->appendChild( $element );
            }
            else
            {
                $lastChild = $prev->lastChild;
                if ( $lastChild->nodeName != 'paragraph' )
                {
                    $para = $this->document->createElement( 'paragraph' );
                    $element = $parent->removeChild( $element );
                    $prev->appendChild( $element );
                    $ret['result'] = $para;
                }
                else
                {
                    $element = $parent->removeChild( $element );
                    $lastChild->appendChild( $element );
                    $ret['result'] = $lastChild;
                }
                return $ret;
            }
        }
        if ( $parentName == 'li' )
        {
            $prev = $element->previousSibling;
            if ( $prev )
            {
                $element = $parent->removeChild( $element );
                $prev->appendChild( $element );
                $ret['result'] = $prev;
                return $ret;
            }
        }
        $ret = $this->appendParagraph( $element, $params );

        return $ret;
    }

    // Structure handler for #text
    protected function structHandlerText( $element, &$newParent )
    {
        $ret = null;
        $parent = $element->parentNode;
        if ( !$parent )
        {
            return $ret;
        }

        // Remove empty text elements
        if ( $element->textContent == '' )
        {
            $element = $parent->removeChild( $element );
            return $ret;
        }

        $ret = $this->appendLineParagraph( $element, $newParent );

        // Left trim spaces:
        if ( $this->getOption( self::OPT_TRIM_SPACES ) )
        {
            $trim = false;
            $currentElement = $element;

            // Check if it is the first element in line
            do
            {
                $prev = $currentElement->previousSibling;
                if ( $prev )
                {
                    break;
                }

                $currentElement = $currentElement->parentNode;

                if ( $currentElement instanceof DOMElement &&
                    ( $currentElement->nodeName == 'line' ||
                    $currentElement->nodeName == 'paragraph' ) )
                {
                    $trim = true;
                    break;
                }

            } while ( $currentElement instanceof DOMElement );

            if ( $trim )
            {
                // Trim and remove if empty
                $element->textContent = ltrim( $element->textContent );
                if ( $element->textContent == '' )
                {
                    $parent = $element->parentNode;
                    $element = $parent->removeChild( $element );
                }
            }
        }

        return $ret;
    }

    /**
     * Publish handlers. (called at pass 2)
     */
    // Publish handler for 'paragraph' element.
    protected function publishHandlerParagraph( $element, &$params )
    {
        $ret = null;
        // Removes single line tag
        $line = $element->lastChild;
        if ( $element->childNodes->length == 1 && $line->nodeName == 'line' )
        {
            $lineChildren = array();
            foreach ( $line->childNodes as $lineChildNode )
            {
                $lineChildren[] = $lineChildNode;
            }

            $line = $element->removeChild( $line );
            foreach ( $lineChildren as $lineChild )
            {
                $element->appendChild( $lineChild );
            }
        }

        return $ret;
    }

    // Publish handler for 'link' element.
    protected function publishHandlerLink( $element, &$params )
    {
        $ret = null;

        $href = $element->getAttribute( 'href' );

        if ( $href )
        {
            // ezobject:// link
            if ( preg_match( "@^ezobject://[0-9]+(#.*)?$@", $href ) )
            {
                // check if the referenced Content exists
                if ( $this->getOption( self::OPT_CHECK_EXTERNAL_DATA ) )
                {
                    $url = strtok( $href, '#' );
                    $anchorName = strtok( '#' );
                    $contentId = substr( strrchr( $url, "/" ), 1 );

                    // check if the referenced Content exists
                    if ( $this->getOption( self::OPT_CHECK_EXTERNAL_DATA ) )
                    {
                        if ( !$this->handler->checkContentById( $contentId ))
                        {
                            $this->messages[] = "Object '$contentId' does not exist.";
                        }
                    }

                    $element->setAttribute( 'object_id', $contentId );

                    if ( !in_array( $contentId, $this->linkedObjectIDArray ) )
                    {
                        $this->linkedObjectIDArray[] = $contentId;
                    }
                }
            }

            // eznode:// link
            elseif ( preg_match( "@^eznode://.+(#.*)?$@" , $href ) )
            {
                // check if the referenced Content exists
                if ( $this->getOption( self::OPT_CHECK_EXTERNAL_DATA ) )
                {
                    $objectID = null;
                    $url = strtok( $href, '#' );
                    $anchorName = strtok( '#' );
                    $locationPath = substr( strchr( $url, "/" ), 2 );
                    if ( preg_match( "@^[0-9]+$@", $locationPath ) )
                    {
                        $locationId = $locationPath;
                        $location = $this->handler->getlocationById( $locationId );
                        if ( !$location )
                        {
                            $this->handleError( self::ERROR_DATA, "Location '$locationId' does not exist." );
                        }
                        else
                        {
                            $contentId = $location->contentId;
                        }
                    }
                    else
                    {
                        $location = $this->handler->getLocationByPath( $locationPath );
                        if ( !$location )
                        {
                            $this->handleError( self::ERROR_DATA, "Node '$locationPath' does not exist." );
                        }
                        else
                        {
                            $locationId = $location->id;
                            $contentId = $location->contentId;
                        }
                        $element->setAttribute( 'show_path', 'true' );
                    }
                    $element->setAttribute( 'node_id', $locationId );

                    if ( isset( $contentId ) && !in_array( $contentId, $this->linkedObjectIDArray ) )
                    {
                        $this->linkedObjectIDArray[] = $contentId;
                    }
                }
            }

            // anchor
            elseif ( preg_match( "@^#.*$@" , $href ) )
            {
                $anchorName = substr( $href, 1 );
            }

            // other
            else
            {
                //washing href. single and double quotes replaced with their urlencoded form
                $href = str_replace( array('\'','"'), array('%27','%22'), $href );

                $temp = explode( '#', $href );
                $url = $temp[0];
                if ( isset( $temp[1] ) )
                {
                    $anchorName = $temp[1];
                }

                if ( $url )
                {
                    // Protection from XSS attack
                    if ( preg_match( "/^(java|vb)script:.*/i" , $url ) )
                    {
                        $this->handleError( self::ERROR_DATA, "Using scripts in links is not allowed, link '$url' has been removed" );

                        $element->removeAttribute( 'href' );
                        return $ret;

                    }

                    // mailto: link
                    // @todo Implement
                    /*if ( preg_match( "/^mailto:(.*)/i" , $url, $mailAddr ) &&
                        !eZMail::validate( $mailAddr[1] ) )
                    {
                        $this->handleError( self::ERROR_DATA, "Invalid e-mail address: '$mailAddr[1]'" );

                        $element->removeAttribute( 'href' );
                        return $ret;
                    }*/
                    // Store urlID instead of href
                    $urlID = $this->convertHrefToID( $url );
                    if ( $urlID )
                    {
                        $urlIDAttributeName = 'url_id';

                        $element->setAttribute( $urlIDAttributeName, $urlID );
                    }
                }
            }

            if ( isset( $anchorName ) && $anchorName )
            {
                $element->setAttribute( 'anchor_name', $anchorName );
            }

            $element->removeAttribute( 'href' );
        }

        return $ret;
    }

    protected function convertHrefToID( $href )
    {
        $href = str_replace("&amp;", "&", $href );

        $urlID = eZURL::registerURL( $href );

        if ( !in_array( $urlID, $this->urlIDArray ) )
        {
            $this->urlIDArray[] = $urlID;
        }

        return $urlID;
    }

    // Publish handler for 'embed' element.
    protected function publishHandlerEmbed( $element, &$params )
    {
        $ret = null;

        $href = $element->getAttribute( 'href' );
        //washing href. single and double quotes replaced with their urlencoded form
        $href = str_replace( array('\'','"'), array('%27','%22'), $href );

        if ( $href != null )
        {
            if ( preg_match( "@^ezobject://[0-9]+$@" , $href ) )
            {
                $objectID = substr( strrchr( $href, "/" ), 1 );

                // protection from self-embedding
                if ( $objectID == $this->contentObjectID )
                {
                    $this->handleError( BaseParser::ERROR_DATA, "Object '$objectID' can not be embedded to itself." );

                    $element->removeAttribute( 'href' );
                    return $ret;
                }

                $element->setAttribute( 'object_id', $objectID );

                if ( !in_array( $objectID, $this->relatedObjectIDArray ) )
                {
                    $this->relatedObjectIDArray[] = $objectID;
                }
            }
            elseif ( preg_match( "@^eznode://.+$@" , $href ) )
            {
                $nodePath = substr( strchr( $href, "/" ), 2 );

                if ( preg_match( "@^[0-9]+$@", $nodePath ) )
                {
                    $nodeID = $nodePath;
                    $location = $this->handler->getLocationById( $nodeID );
                    if ( !$location )
                    {
                        $this->handleError( BaseParser::ERROR_DATA, "Location '$nodeID' does not exist." );

                        $element->removeAttribute( 'href' );
                        return $ret;
                    }
                }
                else
                {
                    $location = $this->handler->getLocationByPath( $nodePath );
                    if ( !$location )
                    {
                        $this->handleError( BaseParser::ERROR_DATA, "Location '$nodePath' does not exist." );

                        $element->removeAttribute( 'href' );
                        return $ret;
                    }
                    $nodeID = $location->id;
                    $element->setAttribute( 'show_path', 'true' );
                }

                $element->setAttribute( 'node_id', $nodeID );
                $objectID = $location->contentId;

                // protection from self-embedding
                if ( $objectID == $this->contentObjectID )
                {
                    $this->handleError( BaseParser::ERROR_DATA, "Object '$objectID' can not be embedded to itself." );

                    $element->removeAttribute( 'href' );
                    return $ret;
                }

                if ( !in_array( $objectID, $this->relatedObjectIDArray ) )
                {
                    $this->relatedObjectIDArray[] = $objectID;
                }
            }
            else
            {
                $this->isInputValid = false;
                $this->messages[] = 'Invalid reference in &lt;embed&gt; tag. Note that <embed> tag supports only \'eznode\' and \'ezobject\' protocols.';
                $element->removeAttribute( 'href' );
                return $ret;
            }
        }

        $element->removeAttribute( 'href' );
        $this->convertCustomAttributes( $element );
        return $ret;
    }

    // Publish handler for 'object' element.
    protected function publishHandlerObject( $element, &$params )
    {
        $objectID = $element->getAttribute( 'id' );
        // protection from self-embedding
        if ( $objectID == $this->contentObjectID )
        {
            $this->isInputValid = false;
            $this->messages[] = "Object '$objectID' can not be embeded to itself.";
            return;
        }

        if ( !in_array( $objectID, $this->relatedObjectIDArray ) )
        {
            $this->relatedObjectIDArray[] = $objectID;
        }

        // If there are any image object with links.
        $href = $element->getAttributeNS( $this->namespaces['image'], 'ezurl_href' );
        //washing href. single and double quotes inside url replaced with their urlencoded form
        $href = str_replace( array('\'','"'), array('%27','%22'), $href );

        $urlID = $element->getAttributeNS( $this->namespaces['image'], 'ezurl_id' );

        if ( $href != null )
        {
            $urlID = eZURL::registerURL( $href );
            $element->setAttributeNS( $this->namespaces['image'], 'image:ezurl_id', $urlID );
            $element->removeAttributeNS( $this->namespaces['image'], 'ezurl_href' );
        }

        if ( $urlID != null )
        {
            $this->urlIDArray[] = $urlID;
        }

        $this->convertCustomAttributes( $element );
    }

    // Publish handler for 'custom' element.
    protected function publishHandlerCustom( $element, &$params )
    {
        $element->removeAttribute( 'inline' );
        $this->convertCustomAttributes( $element );
    }

    protected function convertCustomAttributes( $element )
    {
        $schemaAttrs = $this->xmlSchema->attributes( $element );
        $attributes = $element->attributes;

        for ( $i = $attributes->length - 1; $i >= 0; $i-- )
        {
            $attr = $attributes->item( $i );
            if ( !$attr->prefix && !in_array( $attr->nodeName, $schemaAttrs ) )
            {
                $element->setAttributeNS( $this->namespaces['custom'], 'custom:' . $attr->name, $element->getAttribute( $attr->name ) );
                $element->removeAttributeNode( $attr );
            }
        }
    }
}
