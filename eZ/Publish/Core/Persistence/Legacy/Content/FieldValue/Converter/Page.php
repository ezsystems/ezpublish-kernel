<?php
/**
 * File containing the Page converter
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\FieldType\Page\Parts;
use DOMDocument;
use DOMElement;

class Page implements Converter
{
    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataText = $value->data === null
            ? null
            : $this->generateXmlString( $value->data );
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = $value->dataText === null
            ? null
            : $this->restoreValueFromXmlString( $value->dataText );
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        $storageDef->dataText1 = ( isset( $fieldDef->fieldTypeConstraints->fieldSettings['defaultLayout'] )
            ? $fieldDef->fieldTypeConstraints->fieldSettings['defaultLayout']
            : '' );
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'defaultLayout' => $storageDef->dataText1,
            )
        );
    }

    /**
     * Returns the name of the index column in the attribute table
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return string
     */
    public function getIndexColumn()
    {
        return false;
    }

    /**
     * Generates XML string from $page object to be stored in storage engine
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Page $page
     *
     * @return string
     */
    public function generateXmlString( $page )
    {
        $dom = new DOMDocument( '1.0', 'utf-8' );
        $dom->formatOutput = true;
        $dom->loadXML( '<page />' );

        $pageNode = $dom->documentElement;

        foreach ( $page->getState() as $attrName => $attrValue )
        {
            switch ( $attrName )
            {
                case 'id':
                    $pageNode->setAttribute( 'id', $attrValue );
                    break;
                case 'zones':
                    foreach ( $page->{$attrName} as $zone )
                    {
                        $pageNode->appendChild( $this->generateZoneXmlString( $zone, $dom ) );
                    }
                    break;
                case 'layout':
                    $node = $dom->createElement( 'zone_layout', $attrValue );
                    $pageNode->appendChild( $node );
                    break;
                case 'attributes':
                    foreach ( $attrValue as $arrayItemKey => $arrayItemValue )
                    {
                        $tmp = $dom->createElement( $arrayItemKey );
                        $tmpValue = $dom->createTextNode( $arrayItemValue );
                        $tmp->appendChild( $tmpValue );
                        $pageNode->appendChild( $tmp );
                    }
                    break;
                case 'zonesById':
                    // Do not store
                    break;
                default:
                    $node = $dom->createElement( $attrName );
                    $nodeValue = $dom->createTextNode( $attrValue );
                    $node->appendChild( $nodeValue );
                    $pageNode->appendChild( $node );
                    break;
            }
        }

        return $dom->saveXML();
    }

    /**
     * Generates XML string for a given $zone object
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Zone $zone
     * @param \DOMDocument $dom
     *
     * @return \DOMElement
     */
    protected function generateZoneXmlString( $zone, DOMDocument $dom )
    {
        $zoneNode = $dom->createElement( 'zone' );
        foreach ( $zone->getState() as $attrName => $attrValue )
        {
            switch ( $attrName )
            {
                case 'id':
                    $zoneNode->setAttribute( 'id', 'id_' . $attrValue );
                    break;
                case 'action':
                    if ( $attrValue !== null )
                    {
                        $zoneNode->setAttribute( 'action', $attrValue );
                    }
                    break;
                case 'identifier':
                    $node = $dom->createElement( 'zone_identifier' );
                    $node->appendChild( $dom->createTextNode( $attrValue ) );
                    $zoneNode->appendChild( $node );
                    break;
                case 'blocks':
                    foreach ( $zone->{$attrName} as $block )
                    {
                        $zoneNode->appendChild( $this->generateBlockXmlString( $block, $dom ) );
                    }
                    break;
                case 'attributes':
                    foreach ( $attrValue as $arrayItemKey => $arrayItemValue )
                    {
                        $tmp = $dom->createElement( $arrayItemKey );
                        $tmpValue = $dom->createTextNode( $arrayItemValue );
                        $tmp->appendChild( $tmpValue );
                        $zoneNode->appendChild( $tmp );
                    }
                    break;
                case 'blocksById':
                    // Do not store
                    break;
                default:
                    $node = $dom->createElement( $attrName );
                    $nodeValue = $dom->createTextNode( $attrValue );
                    $node->appendChild( $nodeValue );
                    $zoneNode->appendChild( $node );
                    break;
            }
        }

        return $zoneNode;
    }

    /**
     * Generates XML string for a given $block object
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     * @param \DOMDocument $dom
     *
     * @return \DOMElement
     */
    protected function generateBlockXmlString( $block, DOMDocument $dom )
    {
        $blockNode = $dom->createElement( 'block' );

        foreach ( $block->getState() as $attrName => $attrValue )
        {
            switch ( $attrName )
            {
                case 'id':
                    $blockNode->setAttribute( 'id', 'id_' . $attrValue );
                    break;
                case 'zoneId':
                    $blockNode->appendChild( $dom->createElement( 'zone_id', $attrValue ) );
                    break;
                case 'action':
                    if ( $attrValue !== null )
                    {
                        $blockNode->setAttribute( 'action', $attrValue );
                    }
                case 'items':
                    foreach ( $block->items as $item )
                    {
                        $itemNode = $this->generateItemXmlString( $item, $dom );
                        if ( $itemNode )
                        {
                            $blockNode->appendChild( $itemNode );
                        }
                    }
                    break;
                case 'rotation':
                case 'customAttributes':
                    if ( $attrValue === null )
                    {
                        continue 2;
                    }

                    $node = $dom->createElement( $attrName );
                    $blockNode->appendChild( $node );

                    foreach ( $attrValue as $arrayItemKey => $arrayItemValue )
                    {
                        $tmp = $dom->createElement( $arrayItemKey );
                        $tmpValue = $dom->createTextNode( $arrayItemValue );
                        $tmp->appendChild( $tmpValue );
                        $node->appendChild( $tmp );
                    }
                    break;
                case 'attributes':
                    foreach ( $attrValue as $arrayItemKey => $arrayItemValue )
                    {
                        $tmp = $dom->createElement( $arrayItemKey );
                        $tmpValue = $dom->createTextNode( $arrayItemValue );
                        $tmp->appendChild( $tmpValue );
                        $blockNode->appendChild( $tmp );
                    }
                    break;
                default:
                    $node = $dom->createElement( $attrName );
                    $nodeValue = $dom->createTextNode( $attrValue );
                    $node->appendChild( $nodeValue );
                    $blockNode->appendChild( $node );
                    break;
            }
        }

        return $blockNode;
    }

    /**
     * Generates XML string for a given $item object
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Item $item
     * @param \DOMDocument $dom
     *
     * @return boolean|\DOMElement
     */
    protected function generateItemXmlString( $item, DOMDocument $dom )
    {
        if ( !$item->XMLStorable )
        {
            return false;
        }

        $itemNode = $dom->createElement( 'item' );

        foreach ( $item->getState() as $attrName => $attrValue )
        {
            switch ( $attrName )
            {
                case 'id':
                    $itemNode->setAttribute( 'id', $attrValue );
                    break;
                case 'action':
                    if ( $attrValue !== null )
                    {
                        $itemNode->setAttribute( 'action', $attrValue );
                    }
                default:
                    $node = $dom->createElement( $attrName );
                    $nodeValue = $dom->createTextNode( $attrValue );
                    $node->appendChild( $nodeValue );
                    $itemNode->appendChild( $node );
                    break;
            }
        }

        return $itemNode;
    }

    /**
     * Restores value from XML string
     *
     * @param string $xmlString
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Page
     */
    public function restoreValueFromXmlString( $xmlString )
    {
        $zones = array();
        $attributes = array();
        $layout = null;

        if ( $xmlString )
        {
            $dom = new DOMDocument( '1.0', 'utf-8' );
            $dom->loadXML( $xmlString );
            $root = $dom->documentElement;

            foreach ( $root->childNodes as $node )
            {
                if ( $node->nodeType !== XML_ELEMENT_NODE )
                    continue;

                switch ( $node->nodeName )
                {
                    case 'zone':
                        $zone = $this->restoreZoneFromXml( $node );
                        $zones[] = $zone;
                        break;
                    case 'zone_layout':
                        $layout = $node->nodeValue;
                        break;
                    default:
                        $attributes[$node->nodeName] = $node->nodeValue;
                        break;
                }
            }

            if ( $root->hasAttributes() )
            {
                foreach ( $root->attributes as $attr )
                {
                    $attributes[$attr->name] = $attr->value;
                }
            }
        }

        return $page = new Parts\Page(
            array(
                'zones'        => $zones,
                'layout'       => $layout,
                'attributes'   => $attributes
            )
        );
    }

    /**
     * Restores value for a given Zone $node
     *
     * @param \DOMElement $node
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Zone
     */
    protected function restoreZoneFromXml( DOMElement $node )
    {
        $zoneId = null;
        $zoneIdentifier = null;
        $action = null;
        $blocks = array();
        $attributes = array();

        if ( $node->hasAttributes() )
        {
            foreach ( $node->attributes as $attr )
            {
                switch ( $attr->name )
                {
                    case 'id':
                        // Stored Id has following format : id_<zoneId>, so extract <zoneId>
                        $zoneId = substr(
                            $attr->value,
                            strpos( $attr->value, '_' ) + 1
                        );
                        break;
                    case 'action':
                        $action = $attr->value;
                        break;
                    default:
                        $attributes[$attr->name] = $attr->value;
                }
            }
        }

        foreach ( $node->childNodes as $node )
        {
            if ( $node->nodeType !== XML_ELEMENT_NODE )
                continue;

            switch ( $node->nodeName )
            {
                case 'block':
                    $block = $this->restoreBlockFromXml( $node );
                    $blocks[] = $block;
                    break;
                case 'zone_identifier':
                    $zoneIdentifier = $node->nodeValue;
                    break;
                default:
                    $attributes[$node->nodeName] = $node->nodeValue;
            }
        }

        return new Parts\Zone(
            array(
                'id'            => $zoneId,
                'identifier'    => $zoneIdentifier,
                'attributes'    => $attributes,
                'action'        => $action,
                'blocks'        => $blocks
            )
        );
    }

    /**
     * Restores value for a given Block $node
     *
     * @param \DOMElement $node
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Block
     */
    protected function restoreBlockFromXml( DOMElement $node )
    {
        $blockId = null;
        $items = array();
        $rotation = null;
        $customAttributes = null;
        $attributes = array();
        $name = null;
        $type = null;
        $view = null;
        $overflowId = null;
        $action = null;
        $zoneId = null;

        if ( $node->hasAttributes() )
        {
            foreach ( $node->attributes as $attr )
            {
                switch ( $attr->name )
                {
                    case 'id':
                        // Stored Id has following format : id_<blockId>, so extract <blockId>
                        $blockId = substr(
                            $attr->value,
                            strpos( $attr->value, '_' ) + 1
                        );
                        break;
                    case 'action':
                        $action = $attr->value;
                        break;
                    default:
                        $attributes[$attr->name] = $attr->value;
                }
            }
        }

        foreach ( $node->childNodes as $node )
        {
            if ( $node->nodeType !== XML_ELEMENT_NODE )
                continue;

            switch ( $node->nodeName )
            {
                case 'item':
                    $items[] = $this->restoreItemFromXml( $node );
                    break;
                case 'rotation':
                    if ( $rotation === null )
                        $rotation = array();

                    foreach ( $node->childNodes as $subNode )
                    {
                        if ( $subNode->nodeType !== XML_ELEMENT_NODE )
                            continue;

                        $rotation[$subNode->nodeName] = $subNode->nodeValue;
                    }
                    break;
                case 'custom_attributes':
                    if ( $customAttributes === null )
                        $customAttributes = array();

                    foreach ( $node->childNodes as $subNode )
                    {
                        if ( $subNode->nodeType !== XML_ELEMENT_NODE )
                        {
                            continue;
                        }

                        $customAttributes[$subNode->nodeName] = $subNode->nodeValue;
                    }
                    break;
                case 'name':
                case 'type':
                case 'view':
                    ${$node->nodeName} = $node->nodeValue;
                    break;
                case 'overflow_id':
                    $overflowId = $node->nodeValue;
                    break;
                case 'zone_id':
                    $zoneId = $node->nodeValue;
                    break;
                default:
                    $attributes[$node->nodeName] = $node->nodeValue;
            }
        }

        return new Parts\Block(
            array(
                'id'                => $blockId,
                'action'            => $action,
                'items'             => $items,
                'rotation'          => $rotation,
                'customAttributes'  => $customAttributes,
                'attributes'        => $attributes,
                'name'              => $name,
                'type'              => $type,
                'view'              => $view,
                'overflowId'        => $overflowId,
                'zoneId'            => $zoneId
            )
        );
    }

    /**
     * Restores value for a given Item $node
     *
     * @param \DOMElement $node
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item
     */
    protected function restoreItemFromXml( DOMElement $node )
    {
        $itemId = null;
        $action = null;
        $attributes = array();

        if ( $node->hasAttributes() )
        {
            foreach ( $node->attributes as $attr )
            {
                switch ( $attr->name )
                {
                    case 'id':
                        $itemId = $attr->value;
                        break;
                    case 'action':
                        $action = $attr->value;
                        break;
                    default:
                        $attributes[$attr->name] = $attr->value;
                }
            }
        }

        foreach ( $node->childNodes as $node )
        {
            if ( $node->nodeType !== XML_ELEMENT_NODE )
                continue;

            $item[$node->nodeName] = $node->nodeValue;
        }

        return new Parts\Item(
            array(
                'id'            => $itemId,
                'action'        => $action,
                'attributes'    => $attributes
            )
        );
    }
}
