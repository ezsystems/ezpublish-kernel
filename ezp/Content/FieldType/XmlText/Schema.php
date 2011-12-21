<?php
/**
 * File containing the \ezp\Content\FieldType\XmlText\Schema class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\XmlText;

use ezp\Base\Configuration;

/**
 * @internal
 */
class Schema
{
    /**
     * Schema contents
     * @var array
     */
    private $schema = array(
        'section'   => array( 'blockChildrenAllowed' => array( 'header', 'paragraph', 'section' ),
                              'inlineChildrenAllowed' => false,
                              'childrenRequired' => false,
                              'isInline' => false,
                              'attributes' => array( 'xmlns:image', 'xmlns:xhtml', 'xmlns:custom', 'xmlns:tmp' ) ),

        'embed'     => array( 'blockChildrenAllowed' => false,
                              'inlineChildrenAllowed' => false,
                              'childrenRequired' => null,
                              'isInline' => true,
                              'attributes' => array( 'object_id', 'node_id', 'show_path', 'size',
                                                     'align', 'view', 'xhtml:id', 'class', 'target' ),
                              'attributesDefaults' => array( 'align' => '', 'view' => 'embed', 'class' => '' ) ),

        'embed-inline' => array( 'blockChildrenAllowed' => false,
                              'inlineChildrenAllowed' => false,
                              'childrenRequired' => null,
                              'isInline' => true,
                              'attributes' => array( 'object_id', 'node_id', 'show_path', 'size',
                                                     'align', 'view', 'xhtml:id', 'class', 'target' ),
                              'attributesDefaults' => array( 'align' => '', 'view' => 'embed-inline', 'class' => '' ) ),

        'table'     => array( 'blockChildrenAllowed' => array( 'tr' ),
                              'inlineChildrenAllowed' => false,
                              'childrenRequired' => true,
                              'isInline' => false,
                              'attributes' => array( 'class', 'width', 'border', 'align' ) ),

        'tr'        => array( 'blockChildrenAllowed' => array( 'td', 'th' ),
                              'inlineChildrenAllowed' => false,
                              'childrenRequired' => false,
                              'isInline' => false,
                              'attributes' => array( 'class' ) ),

        'td'        => array( 'blockChildrenAllowed' => array( 'header', 'paragraph', 'section', 'table' ),
                              'inlineChildrenAllowed' => false,
                              'childrenRequired' => false,
                              'isInline' => false,
                              'attributes' => array( 'class', 'align', 'xhtml:width', 'xhtml:colspan', 'xhtml:rowspan' ) ),

        'th'        => array( 'blockChildrenAllowed' => array( 'header', 'paragraph', 'section', 'table' ),
                              'inlineChildrenAllowed' => false,
                              'childrenRequired' => false,
                              'isInline' => false,
                              'attributes' => array( 'class', 'align', 'xhtml:width', 'xhtml:colspan', 'xhtml:rowspan' ) ),

        'ol'        => array( 'blockChildrenAllowed' => array( 'li' ),
                              'inlineChildrenAllowed' => false,
                              'childrenRequired' => true,
                              'isInline' => false,
                              'attributes' => array( 'class' ) ),

        'ul'        => array( 'blockChildrenAllowed' => array( 'li' ),
                              'inlineChildrenAllowed' => false,
                              'childrenRequired' => true,
                              'isInline' => false,
                              'attributes' => array( 'class' ) ),

        'li'        => array( 'blockChildrenAllowed' => array( 'paragraph' ),
                              'inlineChildrenAllowed' => false,
                              'childrenRequired' => true,
                              'isInline' => false,
                              'attributes' => array( 'class' ) ),

        'header'    => array( 'blockChildrenAllowed' => false,
                              'inlineChildrenAllowed' => true,
                              'childrenRequired' => true,
                              'isInline' => false,
                              'attributes' => array( 'class', 'anchor_name', 'align' ) ),

        'paragraph' => array( 'blockChildrenAllowed' => array( 'line', 'link', 'embed', 'table', 'ol', 'ul', 'custom', 'literal' ),
                              'inlineChildrenAllowed' => true,
                              'childrenRequired' => true,
                              'isInline' => false,
                              'attributes' => array( 'class', 'align' ) ),

        'line'      => array( 'blockChildrenAllowed' => false,
                              'inlineChildrenAllowed' => true,
                              'childrenRequired' => true,
                              'isInline' => false,
                              'attributes' => false ),

        'literal'   => array( 'blockChildrenAllowed' => false,
                              'inlineChildrenAllowed' => array( '#text' ),
                              'childrenRequired' => true,
                              'isInline' => false,
                              'attributes' => array( 'class' ) ),

        'strong'    => array( 'blockChildrenAllowed' => false,
                              'inlineChildrenAllowed' => true,
                              'childrenRequired' => true,
                              'isInline' => true,
                              'attributes' => array( 'class' ) ),

        'emphasize' => array( 'blockChildrenAllowed' => false,
                              'inlineChildrenAllowed' => true,
                              'childrenRequired' => true,
                              'isInline' => true,
                              'attributes' => array( 'class' ) ),

        'link'      => array( 'blockChildrenAllowed' => false,
                              'inlineChildrenAllowed' => true,
                              'childrenRequired' => true,
                              'isInline' => true,
                              'attributes' => array( 'class', 'xhtml:id', 'target', 'xhtml:title',
                                                     'object_id', 'node_id', 'show_path', 'anchor_name',
                                                     'url_id', 'id', 'view' ),
                              'attributesDefaults' => array( 'target' => '_self' ) ),

        'anchor'    => array( 'blockChildrenAllowed' => false,
                              'inlineChildrenAllowed' => false,
                              'childrenRequired' => false,
                              'isInline' => true,
                              'attributes' => array( 'name' ) ),

        'custom'    => array( 'blockChildrenAllowed' => true,
                              'inlineChildrenAllowed' => true,
                              'childrenRequired' => false,
                              'isInline' => null,
                              'attributes' => array( 'name', 'align' ) ),

        '#text'     => array( 'blockChildrenAllowed' => false,
                              'inlineChildrenAllowed' => false,
                              'childrenRequired' => false,
                              'isInline' => true,
                              'attributes' => false )
    );

    /**
     * @var \ezp\Content\FieldType\XmlText\Schema
     */
    private static $instance;

    function __construct()
    {
        $configuration = Configuration::getInstance( 'content' );

        // Get inline custom tags list
        $this->schema['custom']['isInline'] = $configuration->get( 'CustomTagSettings', 'IsInline',
            array( 'strike' => 'true', 'sub' => true, 'sup' => true )
        );
        if ( !is_array( $this->schema['custom']['isInline'] ) )
            $this->schema['custom']['isInline'] = array();

        $this->schema['custom']['tagList'] = $configuration->get( 'CustomTagSettings', 'AvailableCustomTags',
            array( 'factbox', 'quote', 'strike', 'sub', 'sup' )
        );
        if ( !is_array( $this->schema['custom']['tagList'] ) )
            $this->schema['custom']['tagList'] = array();

        $eZPublishVersion = 4.6; // eZPublishSDK::majorVersion() + eZPublishSDK::minorVersion() * 0.1;

        // Get all tags available classes list
        // @todo Implement
        foreach ( array_keys( $this->schema ) as $tagName )
        {
            if ( $configuration->has( $tagName, 'AvailableClasses' ) )
            {
                $avail = $configuration->get( $tagName, 'AvailableClasses' );
                if ( is_array( $avail ) && count( $avail ) )
                    $this->schema[$tagName]['classesList'] = $avail;
                else
                    $this->schema[$tagName]['classesList'] = array();
            }
            else
                $this->schema[$tagName]['classesList'] = array();
        }


        // Fix for empty paragraphs setting
        $allowEmptyParagraph = $configuration->get( 'paragraph', 'AllowEmpty', 'false' );
        $this->schema['paragraph']['childrenRequired'] = $allowEmptyParagraph == 'true' ? false : true;

        // Get all tags custom attributes list
        // @todo Implement
        foreach ( array_keys( $this->schema ) as $tagName )
        {
            if ( $tagName == 'custom' )
            {
                // Custom attributes of custom tags
                foreach ( $this->schema['custom']['tagList'] as $customTagName )
                {
                    if ( $configuration->has( $customTagName, 'CustomAttributes' ) )
                    {
                        $avail = $configuration->get( $customTagName, 'CustomAttributes' );
                        if ( is_array( $avail ) && count( $avail ) )
                            $this->schema['custom']['customAttributes'][$customTagName] = $avail;
                        else
                            $this->schema['custom']['customAttributes'][$customTagName] = array();
                    }
                    else
                        $this->schema['custom']['customAttributes'][$customTagName] = array();
                }
            }
            else
            {
                // Custom attributes of regular tags
                if ( $configuration->has( $tagName, 'CustomAttributes' ) )
                {
                    $avail = $configuration->get( $tagName, 'CustomAttributes' );
                    if ( is_array( $avail ) && count( $avail ) )
                        $this->schema[$tagName]['customAttributes'] = $avail;
                    else
                        $this->schema[$tagName]['customAttributes'] = array();
                }
                else
                    $this->schema[$tagName]['customAttributes'] = array();
            }
        }
    }

    /**
     * Returns a shared instance of the eZXMLSchema class.
     *
     * @return eZXMLSchema
     */
    public static function getInstance()
    {
        if ( !self::$instance instanceof self )
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    // Determines if the tag is inline
    function isInline( $element )
    {
        if ( is_string( $element ) )
            $elementName = $element;
        else
            $elementName = $element->nodeName;

        $isInline = $this->schema[$elementName]['isInline'];

        // Special workaround for custom tags.
        if ( is_array( $isInline ) && !is_string( $element ) )
        {
            $isInline = false;
            $name = $element->getAttribute( 'name' );

            if ( isset( $this->schema['custom']['isInline'][$name] ) )
            {
                if ( $this->schema['custom']['isInline'][$name] != 'false' )
                    $isInline = true;
            }
        }
        return $isInline;
    }

    /**
     * Checks if one element is allowed to be a child of another
     *
     * @param $parent parent element: DOMNode or string
     * @param $child child element: DOMNode or string
     *
     * @return bool|null true if elements match schema, false if elements don't match schema, null  in case of errors
     * @todo Add exceptions
    */
    function check( $parent, $child )
    {
        if ( is_string( $parent ) )
            $parentName = $parent;
        else
            $parentName = $parent->nodeName;

        if ( is_string( $child ) )
            $childName = $child;
        else
            $childName = $child->nodeName;

        if ( isset( $this->schema[$childName] ) )
        {
            $isInline = $this->isInline( $child );

            if ( $isInline === true )
            {
                $allowed = $this->schema[$parentName]['inlineChildrenAllowed'];
            }
            elseif ( $isInline === false )
            {
                // Special workaround for custom tags.
                if ( $parentName == 'custom' && !is_string( $parent ) &&
                     $parent->getAttribute( 'inline' ) != 'true' )
                {
                    $allowed = true;
                }
                else
                    $allowed = $this->schema[$parentName]['blockChildrenAllowed'];
            }
            else
                return true;

            if ( is_array( $allowed ) )
                $allowed = in_array( $childName, $allowed );

            if ( !$allowed )
                return false;
        }
        else
        {
            return null;
        }
        return true;
    }

    function childrenRequired( $element )
    {
        //if ( !isset( $this->schema[$element->nodeName] ) )
        //    return false;

        return $this->schema[$element->nodeName]['childrenRequired'];
    }

    function hasAttributes( $element )
    {
        //if ( !isset( $this->schema[$element->nodeName] ) )
        //    return false;

        return ( $this->schema[$element->nodeName]['attributes'] != false );
    }

    function attributes( $element )
    {
        return $this->schema[$element->nodeName]['attributes'];
    }

    function customAttributes( $element )
    {
        if ( is_string( $element ) )
        {
            return $this->schema[$element]['customAttributes'];
        }
        else
        {
            if ( $element->nodeName == 'custom' )
            {
                $name = $element->getAttribute( 'name' );
                if ( $name )
                    return $this->schema['custom']['customAttributes'][$name];
            }
            else
            {
                return $this->schema[$element->nodeName]['customAttributes'];
            }
        }
        return array();
    }

    function attrDefaultValue( $tagName, $attrName )
    {
        if ( isset( $this->schema[$tagName]['attributesDefaults'][$attrName] ) )
            return $this->schema[$tagName]['attributesDefaults'][$attrName];
        else
            return array();
    }

    function attrDefaultValues( $tagName )
    {
        if ( isset( $this->schema[$tagName]['attributesDefaults'] ) )
            return $this->schema[$tagName]['attributesDefaults'];
        else
            return array();
    }

    function exists( $element )
    {
        if ( is_string( $element ) )
        {
            return isset( $this->schema[$element] );
        }
        else
        {
            if ( $element->nodeName == 'custom' )
            {
                $name = $element->getAttribute( 'name' );
                if ( $name )
                    return in_array( $name, $this->schema['custom']['tagList'] );
            }
            else
            {
                return isset( $this->schema[$element->nodeName] );
            }
        }
        return false;
    }

    function availableElements()
    {
        return array_keys( $this->schema );
    }

    function getClassesList( $tagName )
    {
        if ( isset( $this->schema[$tagName]['classesList'] ) )
            return $this->schema[$tagName]['classesList'];
        else
            return array();
    }

    function addAvailableClass( $tagName, $class )
    {
        if ( !isset( $this->schema[$tagName]['classesList'] ) )
            $this->schema[$tagName]['classesList'] = array();

        $this->schema[$tagName]['classesList'][] = $class;
    }

    function addCustomAttribute( $element, $attrName )
    {
        if ( is_string( $element ) )
        {
            $this->schema[$element]['customAttributes'][] = $attrName;
        }
        else
        {
            if ( $element->nodeName == 'custom' )
            {
                $name = $element->getAttribute( 'name' );
                if ( $name )
                    $this->schema['custom']['customAttributes'][$name][] = $attrName;
            }
            else
            {
                $this->schema[$element->nodeName]['customAttributes'][] = $attrName;
            }
        }
    }
}
?>
