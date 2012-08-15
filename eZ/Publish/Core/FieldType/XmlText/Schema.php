<?php
/**
 * File containing the \eZ\Publish\Core\FieldType\XmlText\Schema class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText;

use DOMElement;
use DOMNode;

/**
 * @internal
 *
 * @todo Change data structure of attribute and classes to include human readable name,
 *       but those will need to be translated.
 */
class Schema
{
    /**
     * Schema contents
     *
     * @var array
     */
    private $schema = array(
        'section' => array(
            'blockChildrenAllowed' => array( 'header', 'paragraph', 'section' ),
            'inlineChildrenAllowed' => false,
            'childrenRequired' => false,
            'isInline' => false,
            'customAttributes' => array(),
            'attributes' => array( 'xmlns:image', 'xmlns:xhtml', 'xmlns:custom', 'xmlns:tmp' )
        ),
        'embed' => array(
            'blockChildrenAllowed' => false,
            'inlineChildrenAllowed' => false,
            'childrenRequired' => null,
            'isInline' => true,
            'customAttributes' => array(),
            'attributes' => array( 'object_id', 'node_id', 'show_path', 'size', 'align', 'view', 'xhtml:id', 'class', 'target' ),
            'attributesDefaults' => array( 'align' => '', 'view' => 'embed', 'class' => '' )
        ),
        'embed-inline' => array(
            'blockChildrenAllowed' => false,
            'inlineChildrenAllowed' => false,
            'childrenRequired' => null,
            'isInline' => true,
            'customAttributes' => array(),
            'attributes' => array( 'object_id', 'node_id', 'show_path', 'size', 'align', 'view', 'xhtml:id', 'class', 'target' ),
            'attributesDefaults' => array( 'align' => '', 'view' => 'embed-inline', 'class' => '' )
        ),
        'table' => array(
            'blockChildrenAllowed' => array( 'tr' ),
            'inlineChildrenAllowed' => false,
            'childrenRequired' => true,
            'isInline' => false,
            'customAttributes' => array(),
            'attributes' => array( 'class', 'width', 'border', 'align' )
        ),
        'tr' => array(
            'blockChildrenAllowed' => array( 'td', 'th' ),
            'inlineChildrenAllowed' => false,
            'childrenRequired' => false,
            'isInline' => false,
            'customAttributes' => array(),
            'attributes' => array( 'class' )
        ),
        'td' => array(
            'blockChildrenAllowed' => array( 'header', 'paragraph', 'section', 'table' ),
            'inlineChildrenAllowed' => false,
            'childrenRequired' => false,
            'isInline' => false,
            'customAttributes' => array(),
            'attributes' => array( 'class', 'align', 'xhtml:width', 'xhtml:colspan', 'xhtml:rowspan' )
        ),
        'th' => array(
            'blockChildrenAllowed' => array( 'header', 'paragraph', 'section', 'table' ),
            'inlineChildrenAllowed' => false,
            'childrenRequired' => false,
            'isInline' => false,
            'customAttributes' => array(),
            'attributes' => array( 'class', 'align', 'xhtml:width', 'xhtml:colspan', 'xhtml:rowspan' )
        ),
        'ol' => array(
            'blockChildrenAllowed' => array( 'li' ),
            'inlineChildrenAllowed' => false,
            'childrenRequired' => true,
            'isInline' => false,
            'customAttributes' => array(),
            'attributes' => array( 'class' )
        ),
        'ul' => array(
            'blockChildrenAllowed' => array( 'li' ),
            'inlineChildrenAllowed' => false,
            'childrenRequired' => true,
            'isInline' => false,
            'customAttributes' => array(),
            'attributes' => array( 'class' )
        ),
        'li' => array(
            'blockChildrenAllowed' => array( 'paragraph' ),
            'inlineChildrenAllowed' => false,
            'childrenRequired' => true,
            'isInline' => false,
            'customAttributes' => array(),
            'attributes' => array( 'class' )
        ),
        'header' => array(
            'blockChildrenAllowed' => false,
            'inlineChildrenAllowed' => true,
            'childrenRequired' => true,
            'isInline' => false,
            'customAttributes' => array(),
            'attributes' => array( 'class', 'anchor_name', 'align' )
        ),
        'paragraph' => array(
            'blockChildrenAllowed' => array( 'line', 'link', 'embed', 'table', 'ol', 'ul', 'custom', 'literal' ),
            'inlineChildrenAllowed' => true,
            'childrenRequired' => false,
            'isInline' => false,
            'customAttributes' => array(),
            'attributes' => array( 'class', 'align' )
        ),
        'line' => array(
            'blockChildrenAllowed' => false,
            'inlineChildrenAllowed' => true,
            'childrenRequired' => true,
            'isInline' => false,
            'customAttributes' => array(),
            'attributes' => false
        ),
        'literal' => array(
            'blockChildrenAllowed' => false,
            'inlineChildrenAllowed' => array( '#text' ),
            'childrenRequired' => true,
            'isInline' => false,
            'customAttributes' => array(),
            'attributes' => array( 'class' )
        ),
        'strong' => array(
            'blockChildrenAllowed' => false,
            'inlineChildrenAllowed' => true,
            'childrenRequired' => true,
            'isInline' => true,
            'customAttributes' => array(),
            'attributes' => array( 'class' )
        ),
        'emphasize' => array(
            'blockChildrenAllowed' => false,
            'inlineChildrenAllowed' => true,
            'childrenRequired' => true,
            'isInline' => true,
            'customAttributes' => array(),
            'attributes' => array( 'class' )
        ),
        'link' => array(
            'blockChildrenAllowed' => false,
            'inlineChildrenAllowed' => true,
            'childrenRequired' => true,
            'isInline' => true,
            'customAttributes' => array(),
            'attributes' => array( 'class', 'xhtml:id', 'target', 'xhtml:title', 'object_id', 'node_id', 'show_path', 'anchor_name', 'url_id', 'id', 'view' ),
            'attributesDefaults' => array( 'target' => '_self' )
        ),
        'anchor' => array(
            'blockChildrenAllowed' => false,
            'inlineChildrenAllowed' => false,
            'childrenRequired' => false,
            'isInline' => true,
            'customAttributes' => array(),
            'attributes' => array( 'name' )
        ),
        'custom' => array(
            'blockChildrenAllowed' => true,
            'inlineChildrenAllowed' => true,
            'childrenRequired' => false,
            'isInline' => false,
            'customAttributes' => array(),
            'tags' => array(), // key: custom tag name, value: overrides for the custom tag props
            'attributes' => array( 'name', 'align' )
        ),
        '#text' => array(
            'blockChildrenAllowed' => false,
            'inlineChildrenAllowed' => false,
            'childrenRequired' => false,
            'isInline' => true,
            'customAttributes' => array(),
            'attributes' => false
        )
    );

    /**
     * @param array $settings
     */
    public function __construct( array $settings = array() )
    {
        // Apply default values
        $settings = $settings + array(
            'customAttributes' => array(),
            'classesList' => array(),
            'customTags' => array( // Custom tags can override any of the base custom tags values except attributes
                'factbox' => array(),
                'quote' => array(),
                'strike' => array( 'isInline' => true ),
                'sub' => array( 'isInline' => true ),
                'sup' => array( 'isInline' => true )
            ),
        );

        // Apply custom tag settings
        $this->schema['custom']['tags'] = $settings['customTags'];

        // Apply custom attribute settings
        foreach ( $settings['customAttributes'] as $tagName => $customAttributes )
        {
            if ( !isset( $this->schema[$tagName] ) )
            {
                throw new \Exception("Unsupported tag name in customAttributes settings: {$tagName}");
            }

            $this->schema[$tagName]['customAttributes'] = $customAttributes;
        }

        // Apply classes allowed pr tag
        foreach ( $settings['classesList'] as $tagName => $classesList )
        {
            if ( !isset( $this->schema[$tagName] ) )
                throw new \Exception("Unsupported tag name in classesList settings: {$tagName}");

            $this->schema[$tagName]['classesList'] = $classesList;
        }
    }

    //
    /**
     * Determines if the tag is inline
     *
     * @param \DOMNode|\DOMElement $element
     *
     * @return bool
     */
    public function isInline( DOMNode $element )
    {
        // Use specific custom tag setting if set
        if ( $element->nodeName === 'custom' && $element instanceof DOMElement )
        {
            $name = $element->getAttribute( 'name' );
            if ( isset( $this->schema['custom']['tags'][$name]['isInline'] ) )
            {
                return $this->schema['custom']['tags'][$name]['isInline'];
            }
            // fallback to settings on base custom tag
        }
        return $this->schema[$element->nodeName]['isInline'];
    }

    /**
     * Checks if one element is allowed to be a child of another
     *
     * @param \DOMNode|\DOMElement $parent Parent element
     * @param \DOMNode|\DOMElement|string $child Child element
     *
     * @return bool|null true if elements match schema, false if elements don't match schema, null  in case of errors
     * @todo Add exceptions
     */
    public function check( DOMNode $parent, $child )
    {
        $parentName = $parent->nodeName;

        if ( $child instanceof DOMNode )
            $childName = $child->nodeName;
        else
            $childName = $child;

        if ( isset( $this->schema[$childName] ) )
        {
            $isInline = $this->isInline( $child );

            if ( $isInline === true )
            {
                $allowed = $this->schema[$parentName]['inlineChildrenAllowed'];
            }
            elseif ( $isInline === false )
            {
                // Special logic for custom tags.
                if ( $parentName === 'custom' &&
                    $parent instanceof DOMElement &&
                    $parent->getAttribute( 'inline' ) !== 'true'
                )
                {
                    $allowed = true;
                }
                else
                {
                    $allowed = $this->schema[$parentName]['blockChildrenAllowed'];
                }
            }
            else
            {
                return true;
            }

            if ( $allowed !== false && $allowed !== true )
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

    /**
     * @param \DOMNode|\DOMElement $element
     *
     * @return bool
     */
    public function childrenRequired( DOMNode $element )
    {
        // Use specific custom tag setting if set
        if ( $element->nodeName === 'custom' && $element instanceof DOMElement )
        {
            $name = $element->getAttribute( 'name' );
            if ( isset( $this->schema['custom']['tags'][$name]['childrenRequired'] ) )
            {
                return $this->schema['custom']['tags'][$name]['childrenRequired'];
            }
            // fallback to settings on base custom tag
        }

        return $this->schema[$element->nodeName]['childrenRequired'];
    }

    /**
     * @param \DOMNode|\DOMElement $element
     *
     * @return bool
     */
    public function hasAttributes( DOMNode $element )
    {
        // Use specific custom tag setting if set
        if ( $element->nodeName === 'custom' && $element instanceof DOMElement )
        {
            $name = $element->getAttribute( 'name' );
            if ( isset( $this->schema['custom']['tags'][$name]['attributes'] ) )
            {
                return !empty( $this->schema['custom']['tags'][$name]['attributes'] );
            }
            // fallback to settings on base custom tag
        }

        return !empty( $this->schema[$element->nodeName]['attributes'] );
    }

    /**
     * @param \DOMNode|\DOMElement $element
     *
     * @return array
     */
    public function attributes( DOMNode $element )
    {
        // Use specific custom tag setting if set
        if ( $element->nodeName === 'custom' && $element instanceof DOMElement )
        {
            $name = $element->getAttribute( 'name' );
            if ( isset( $this->schema['custom']['tags'][$name]['attributes'] ) )
            {
                return $this->schema['custom']['tags'][$name]['attributes'];
            }
            // fallback to settings on base custom tag
        }

        return $this->schema[$element->nodeName]['attributes'];
    }

    /**
     * @param \DOMNode|\DOMElement $element
     * @param string $attributeName
     *
     * @return mixed
     */
    public function attributeDefaultValue( DOMNode $element, $attributeName )
    {
        // Use specific custom tag setting if set
        if ( $element->nodeName === 'custom' && $element instanceof DOMElement )
        {
            $name = $element->getAttribute( 'name' );
            if ( isset( $this->schema['custom']['tags'][$name]['attributesDefaults'][$attributeName] ) )
            {
                return $this->schema['custom']['tags'][$name]['attributesDefaults'][$attributeName];
            }
            // fallback to settings on base custom tag
        }

        if ( isset( $this->schema[$element->nodeName]['attributesDefaults'][$attributeName] ) )
            return $this->schema[$element->nodeName]['attributesDefaults'][$attributeName];

        return null;
    }

    /**
     * @param \DOMNode|\DOMElement $element
     *
     * @return array
     */
    public function attributeDefaultValues( DOMNode $element )
    {
        // Use specific custom tag setting if set
        if ( $element->nodeName === 'custom' && $element instanceof DOMElement )
        {
            $name = $element->getAttribute( 'name' );
            if ( isset( $this->schema['custom']['tags'][$name]['attributesDefaults'] ) )
            {
                return $this->schema['custom']['tags'][$name]['attributesDefaults'];
            }
            // fallback to settings on base custom tag
        }

        if ( isset( $this->schema[$element->nodeName]['attributesDefaults'] ) )
            return $this->schema[$element->nodeName]['attributesDefaults'];

        return array();
    }

    /**
     * @param \DOMNode|\DOMElement $element
     *
     * @return array
     */
    public function customAttributes( DOMNode $element )
    {
        // Use specific custom tag setting if set
        if ( $element->nodeName === 'custom' && $element instanceof DOMElement )
        {
            $name = $element->getAttribute( 'name' );
            if ( isset( $this->schema['custom']['tags'][$name]['customAttributes'] ) )
            {
                return $this->schema['custom']['tags'][$name]['customAttributes'];
            }
            // fallback to settings on base custom tag
        }

        return $this->schema[$element->nodeName]['customAttributes'];
    }

    /**
     * @param \DOMNode|\DOMElement $element
     *
     * @return bool
     */
    public function exists( DOMNode $element )
    {
        if ( $element->nodeName === 'custom' && $element instanceof DOMElement )
        {
            $name = $element->getAttribute( 'name' );
            return $name && isset( $this->schema['custom']['tags'][$name] );
        }

        return isset( $this->schema[$element->nodeName] );
    }

    /**
     * @param \DOMNode|\DOMElement $element
     *
     * @return array
     */
    public function getClassesList( DOMNode $element )
    {
        // Use specific custom tag setting if set
        if ( $element->nodeName === 'custom' && $element instanceof DOMElement )
        {
            $name = $element->getAttribute( 'name' );
            if ( isset( $this->schema['custom']['tags'][$name]['classesList'] ) )
            {
                return $this->schema['custom']['tags'][$name]['classesList'];
            }
            // fallback to settings on base custom tag
        }

        return $this->schema[$element->nodeName]['classesList'];
    }

    /**
     * List of available classes
     *
     * @return array
     */
    public function availableElements()
    {
        return array_keys( $this->schema );
    }
}
