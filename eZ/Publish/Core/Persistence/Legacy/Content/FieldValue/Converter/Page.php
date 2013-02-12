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
use eZ\Publish\Core\FieldType\Page\Service;
use DOMDocument;
use DOMElement;

class Page implements Converter
{
    /**
     * Page service container
     *
     * @var \eZ\Publish\Core\FieldType\Page\Service
     */
    protected $pageService;

    /**
     * Constructor
     *
     * @param \eZ\Publish\Core\FieldType\Page\Service $pageService
     */
    public function __construct( Service $pageService )
    {
        $this->pageService = $pageService;
    }

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
        $success = $dom->loadXML( '<page />' );

        $pageNode = $dom->documentElement;

        foreach ( $page->getProperties() as $attrName => $attrValue )
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
        foreach ( $zone->getProperties() as $attrName => $attrValue )
        {
            switch ( $attrName )
            {
                case 'id':
                    $zoneNode->setAttribute( 'id', 'id_' . $attrValue );
                    break;
                case 'action':
                    $zoneNode->setAttribute( 'action', $attrValue );
                    break;
                case 'blocks':
                    foreach ( $zone->{$attrName} as $block )
                    {
                        $zoneNode->appendChild( $this->generateBlockXmlString( $block, $dom ) );
                    }
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

        foreach ( $block->getProperties() as $attrName => $attrValue )
        {
            switch ( $attrName )
            {
                case 'id':
                    $blockNode->setAttribute( 'id', 'id_' . $attrValue );
                    break;
                case 'action':
                    $blockNode->setAttribute( 'action', $attrValue );
                    break;
                case 'items':
                    foreach ( $block->{$attrName} as $item )
                    {
                        $itemNode = $this->generateItemXmlString( $item, $dom );
                        if ( $itemNode )
                        {
                            $blockNode->appendChild( $itemNode );
                        }
                    }
                    break;
                case 'rotation':
                case 'custom_attributes':
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

        foreach ( $item->getProperties() as $attrName => $attrValue )
        {
            switch ( $attrName )
            {
                case 'id':
                    $itemNode->setAttribute( 'id', $attrValue );
                    break;
                case 'action':
                    $itemNode->setAttribute( 'action', $attrValue );
                    break;
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
        $page = new Parts\Page( $this->pageService );

        if ( $xmlString )
        {
            $dom = new DOMDocument( '1.0', 'utf-8' );
            $success = $dom->loadXML( $xmlString );
            $root = $dom->documentElement;

            foreach ( $root->childNodes as $node )
            {
                if ( $node->nodeType == XML_ELEMENT_NODE && $node->nodeName == 'zone' )
                {
                   $page->addZone( $this->restoreZoneFromXml( $node ) );
                }
                else if ( $node->nodeType == XML_ELEMENT_NODE )
                {
                    $page->{$node->nodeName} = $node->nodeValue;
                }
            }

            if ( $root->hasAttributes() )
            {
                foreach ( $root->attributes as $attr )
                {
                    $page->{$attr->name} = $attr->value;
                }
            }
        }

        return $page;
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
        $zone = new Parts\Zone( $this->pageService );

        if ( $node->hasAttributes() )
        {
            foreach ( $node->attributes as $attr )
            {
                if ( $attr->name == 'id' )
                {
                    $value = explode( '_', $attr->value );
                    $zone->{$attr->name} = $value[1];
                }
                else
                {
                    $zone->{$attr->name} = $attr->value;
                }
            }
        }

        foreach ( $node->childNodes as $node )
        {
            if ( $node->nodeType == XML_ELEMENT_NODE && $node->nodeName == 'block' )
            {
                $zone->addBlock( $this->restoreBlockFromXml( $node ) );
            }
            else if ( $node->nodeType == XML_ELEMENT_NODE )
            {
                $zone->{$node->nodeName} = $node->nodeValue;
            }
        }

        return $zone;
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
        $block = new Parts\Block( $this->pageService );

        if ( $node->hasAttributes() )
        {
            foreach ( $node->attributes as $attr )
            {
                if ( $attr->name == 'id' )
                {
                    $value = explode( '_', $attr->value );
                    $block->{$attr->name} = $value[1];
                }
                else
                {
                    $block->{$attr->name} = $attr->value;
                }
            }
        }

        foreach ( $node->childNodes as $node )
        {
            if ( $node->nodeType == XML_ELEMENT_NODE && $node->nodeName == 'item' )
            {
                $block->addItem( $this->restoreItemFromXml( $node ) );
            }
            else if ( $node->nodeType == XML_ELEMENT_NODE && $node->nodeName == 'rotation' )
            {
                $attrValue = array();

                foreach ( $node->childNodes as $subNode )
                {
                    if ( $subNode->nodeType == XML_ELEMENT_NODE )
                    {
                        $attrValue[$subNode->nodeName] = $subNode->nodeValue;
                    }
                }

                $block->{$node->nodeName} = $attrValue;
            }
            else if ( $node->nodeType == XML_ELEMENT_NODE && $node->nodeName == 'custom_attributes' )
            {
                $attrValue = array();

                foreach ( $node->childNodes as $subNode )
                {
                    if ( $subNode->nodeType == XML_ELEMENT_NODE )
                    {
                        $attrValue[$subNode->nodeName] = $subNode->nodeValue;
                    }
                }

                $block->{$node->nodeName} = $attrValue;
            }
            else
            {
                if ( $node->nodeType == XML_ELEMENT_NODE )
                {
                    $block->{$node->nodeName} = $node->nodeValue;
                }
            }
        }

        return $block;
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
        $item = new Parts\Item( $this->pageService );

        if ( $node->hasAttributes() )
        {
            foreach ( $node->attributes as $attr )
            {
                $item->{$attr->name} = $attr->value;
            }
        }

        foreach ( $node->childNodes as $node )
        {
            if ( $node->nodeType == XML_ELEMENT_NODE )
            {
                $item->{$node->nodeName} = $node->nodeValue;
            }
        }

        return $item;
    }
}
