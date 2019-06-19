<?php

/**
 * File containing the Page converter.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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

class PageConverter implements Converter
{
    /**
     * Converts data from $value to $storageFieldValue.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
        $storageFieldValue->dataText = $value->data === null
            ? null
            : $this->generateXmlString($value->data);
    }

    /**
     * Converts data from $value to $fieldValue.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        $fieldValue->data = $value->dataText === null
            ? null
            : $this->restoreValueFromXmlString($value->dataText);
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        $storageDef->dataText1 = (isset($fieldDef->fieldTypeConstraints->fieldSettings['defaultLayout'])
            ? $fieldDef->fieldTypeConstraints->fieldSettings['defaultLayout']
            : '');
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'defaultLayout' => $storageDef->dataText1,
            ]
        );
    }

    /**
     * Returns the name of the index column in the attribute table.
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
     * Generates XML string from $page object to be stored in storage engine.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Page $page
     *
     * @return string
     */
    public function generateXmlString(Parts\Page $page)
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;
        $dom->loadXML('<page />');

        $pageNode = $dom->documentElement;

        foreach ($page->getState() as $attrName => $attrValue) {
            switch ($attrName) {
                case 'id':
                    $pageNode->setAttribute('id', $attrValue);
                    break;
                case 'zones':
                    foreach ($page->{$attrName} as $zone) {
                        $pageNode->appendChild($this->generateZoneXmlString($zone, $dom));
                    }
                    break;
                case 'layout':
                    $node = $dom->createElement('zone_layout', $attrValue);
                    $pageNode->appendChild($node);
                    break;
                case 'attributes':
                    foreach ($attrValue as $arrayItemKey => $arrayItemValue) {
                        $this->addNewXmlElement($dom, $pageNode, $arrayItemKey, $arrayItemValue);
                    }
                    break;
                case 'zonesById':
                    // Do not store
                    break;
                default:
                    $this->addNewNotEmptyXmlElement($dom, $pageNode, $attrName, $attrValue);
                    break;
            }
        }

        return $dom->saveXML();
    }

    /**
     * Generates XML string for a given $zone object.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Zone $zone
     * @param \DOMDocument $dom
     *
     * @return \DOMElement
     */
    protected function generateZoneXmlString(Parts\Zone $zone, DOMDocument $dom)
    {
        $zoneNode = $dom->createElement('zone');
        foreach ($zone->getState() as $attrName => $attrValue) {
            switch ($attrName) {
                case 'id':
                    $zoneNode->setAttribute('id', 'id_' . $attrValue);
                    break;
                case 'action':
                    if ($attrValue !== null) {
                        $zoneNode->setAttribute('action', $attrValue);
                    }
                    break;
                case 'identifier':
                    $this->addNewXmlElement($dom, $zoneNode, 'zone_identifier', $attrValue);
                    break;
                case 'blocks':
                    foreach ($zone->{$attrName} as $block) {
                        $zoneNode->appendChild($this->generateBlockXmlString($block, $dom));
                    }
                    break;
                case 'attributes':
                    foreach ($attrValue as $arrayItemKey => $arrayItemValue) {
                        $this->addNewXmlElement($dom, $zoneNode, $arrayItemKey, $arrayItemValue);
                    }
                    break;
                case 'blocksById':
                    // Do not store
                    break;
                default:
                    $this->addNewNotEmptyXmlElement($dom, $zoneNode, $attrName, $attrValue);
                    break;
            }
        }

        return $zoneNode;
    }

    /**
     * Generates XML string for a given $block object.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     * @param \DOMDocument $dom
     *
     * @return \DOMElement
     */
    protected function generateBlockXmlString(Parts\Block $block, DOMDocument $dom)
    {
        $blockNode = $dom->createElement('block');

        foreach ($block->getState() as $attrName => $attrValue) {
            switch ($attrName) {
                case 'id':
                    $blockNode->setAttribute('id', 'id_' . $attrValue);
                    break;
                case 'zoneId':
                    $blockNode->appendChild($dom->createElement('zone_id', $attrValue));
                    break;
                case 'action':
                    if ($attrValue !== null) {
                        $blockNode->setAttribute('action', $attrValue);
                    }
                    break;
                case 'items':
                    foreach ($block->items as $item) {
                        $itemNode = $this->generateItemXmlString($item, $dom);
                        if ($itemNode) {
                            $blockNode->appendChild($itemNode);
                        }
                    }
                    break;
                case 'overflowId':
                    $this->addNewXmlElement($dom, $blockNode, 'overflow_id', $attrValue);
                    break;
                case 'rotation':
                    if ($attrValue === null) {
                        continue 2;
                    }

                    $node = $dom->createElement($attrName);
                    $blockNode->appendChild($node);

                    foreach ($attrValue as $arrayItemKey => $arrayItemValue) {
                        $this->addNewXmlElement($dom, $node, $arrayItemKey, $arrayItemValue);
                    }
                    break;
                case 'customAttributes':
                    if ($attrValue === null) {
                        continue 2;
                    }

                    $node = $dom->createElement('custom_attributes');
                    $blockNode->appendChild($node);

                    foreach ($attrValue as $arrayItemKey => $arrayItemValue) {
                        $this->addNewXmlElement($dom, $node, $arrayItemKey, $arrayItemValue);
                    }
                    break;
                case 'attributes':
                    foreach ($attrValue as $arrayItemKey => $arrayItemValue) {
                        $this->addNewXmlElement($dom, $blockNode, $arrayItemKey, $arrayItemValue);
                    }
                    break;
                default:
                    $this->addNewNotEmptyXmlElement($dom, $blockNode, $attrName, $attrValue);
                    break;
            }
        }

        return $blockNode;
    }

    /**
     * Generates XML string for a given $item object.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Item $item
     * @param \DOMDocument $dom
     *
     * @return bool|\DOMElement
     */
    protected function generateItemXmlString(Parts\Item $item, DOMDocument $dom)
    {
        $itemNode = $dom->createElement('item');

        foreach ($item->getState() as $attrName => $attrValue) {
            switch ($attrName) {
                case 'action':
                    if ($attrValue !== null) {
                        $itemNode->setAttribute('action', $attrValue);
                    }
                    break;
                case 'contentId':
                    $this->addNewNotEmptyXmlElement($dom, $itemNode, 'object_id', $attrValue);
                    break;
                case 'locationId':
                    $this->addNewNotEmptyXmlElement($dom, $itemNode, 'node_id', $attrValue);
                    break;
                case 'priority':
                    $this->addNewNotEmptyXmlElement($dom, $itemNode, 'priority', $attrValue);
                    break;
                case 'publicationDate':
                    $this->addNewNotEmptyXmlElement($dom, $itemNode, 'ts_publication', $attrValue);
                    break;
                case 'visibilityDate':
                    $this->addNewNotEmptyXmlElement($dom, $itemNode, 'ts_visible', $attrValue);
                    break;
                case 'hiddenDate':
                    $this->addNewNotEmptyXmlElement($dom, $itemNode, 'ts_hidden', $attrValue);
                    break;
                case 'rotationUntilDate':
                    $this->addNewNotEmptyXmlElement($dom, $itemNode, 'rotation_until', $attrValue);
                    break;
                case 'movedTo':
                    $this->addNewNotEmptyXmlElement($dom, $itemNode, 'moved_to', $attrValue);
                    break;
                case 'attributes':
                    foreach ($attrValue as $arrayItemKey => $arrayItemValue) {
                        $this->addNewNotEmptyXmlElement($dom, $itemNode, $arrayItemKey, $arrayItemValue);
                    }
                    break;
            }
        }

        return $itemNode;
    }

    /**
     * Utility method to add new elements to an xml node if their value is not empty.
     *
     * @param \DOMDocument $dom xml document
     * @param \DOMElement $node where to add the new element
     * @param string $name of the new element
     * @param string $value of the new element
     */
    private function addNewNotEmptyXmlElement(DOMDocument $dom, DOMElement $node, $name, $value)
    {
        if (!empty($value)) {
            $this->addNewXmlElement($dom, $node, $name, $value);
        }
    }

    /**
     * Utility method to add new elements to an xml node.
     *
     * @param \DOMDocument $dom xml document
     * @param \DOMElement $node where to add the new element
     * @param string $name of the new element
     * @param string $value of the new element
     */
    private function addNewXmlElement(DOMDocument $dom, DOMElement $node, $name, $value)
    {
        $new = $dom->createElement($name);
        $new->appendChild($dom->createTextNode($value));
        $node->appendChild($new);
    }

    /**
     * Restores value from XML string.
     *
     * @param string $xmlString
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Page
     */
    public function restoreValueFromXmlString($xmlString)
    {
        $zones = [];
        $attributes = [];
        $layout = null;

        if ($xmlString) {
            $dom = new DOMDocument('1.0', 'utf-8');
            $dom->loadXML($xmlString);
            $root = $dom->documentElement;

            foreach ($root->childNodes as $node) {
                if ($node->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }

                switch ($node->nodeName) {
                    case 'zone':
                        $zone = $this->restoreZoneFromXml($node);
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

            if ($root->hasAttributes()) {
                foreach ($root->attributes as $attr) {
                    $attributes[$attr->name] = $attr->value;
                }
            }
        }

        return new Parts\Page(
            [
                'zones' => $zones,
                'layout' => $layout,
                'attributes' => $attributes,
            ]
        );
    }

    /**
     * Restores value for a given Zone $node.
     *
     * @param \DOMElement $node
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Zone
     */
    protected function restoreZoneFromXml(DOMElement $node)
    {
        $zoneId = null;
        $zoneIdentifier = null;
        $action = null;
        $blocks = [];
        $attributes = [];

        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                switch ($attr->name) {
                    case 'id':
                        // Stored Id has following format : id_<zoneId>, so extract <zoneId>
                        $zoneId = substr(
                            $attr->value,
                            strpos($attr->value, '_') + 1
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

        foreach ($node->childNodes as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            switch ($node->nodeName) {
                case 'block':
                    $block = $this->restoreBlockFromXml($node);
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
            [
                'id' => $zoneId,
                'identifier' => $zoneIdentifier,
                'attributes' => $attributes,
                'action' => $action,
                'blocks' => $blocks,
            ]
        );
    }

    /**
     * Restores value for a given Block $node.
     *
     * @param \DOMElement $node
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Block
     */
    protected function restoreBlockFromXml(DOMElement $node)
    {
        $blockId = null;
        $items = [];
        $rotation = null;
        $customAttributes = null;
        $attributes = [];
        $name = null;
        $type = null;
        $view = null;
        $overflowId = null;
        $action = null;
        $zoneId = null;

        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                switch ($attr->name) {
                    case 'id':
                        // Stored Id has following format : id_<blockId>, so extract <blockId>
                        $blockId = substr(
                            $attr->value,
                            strpos($attr->value, '_') + 1
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

        foreach ($node->childNodes as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            switch ($node->nodeName) {
                case 'item':
                    $items[] = $this->restoreItemFromXml($node);
                    break;
                case 'rotation':
                    if ($rotation === null) {
                        $rotation = [];
                    }

                    foreach ($node->childNodes as $subNode) {
                        if ($subNode->nodeType !== XML_ELEMENT_NODE) {
                            continue;
                        }

                        $rotation[$subNode->nodeName] = $subNode->nodeValue;
                    }
                    break;
                case 'custom_attributes':
                    if ($customAttributes === null) {
                        $customAttributes = [];
                    }

                    foreach ($node->childNodes as $subNode) {
                        if ($subNode->nodeType !== XML_ELEMENT_NODE) {
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
            [
                'id' => $blockId,
                'action' => $action,
                'items' => $items,
                'rotation' => $rotation,
                'customAttributes' => $customAttributes,
                'attributes' => $attributes,
                'name' => $name,
                'type' => $type,
                'view' => $view,
                'overflowId' => $overflowId,
                'zoneId' => $zoneId,
            ]
        );
    }

    /**
     * Restores value for a given Item $node.
     *
     * @param \DOMElement $node
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item
     */
    protected function restoreItemFromXml(DOMElement $node)
    {
        $item = ['attributes' => []];

        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                switch ($attr->name) {
                    case 'action':
                        $item['action'] = $attr->value;
                        break;
                    default:
                        $item['attributes'][$attr->name] = $attr->value;
                }
            }
        }

        foreach ($node->childNodes as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            switch ($node->nodeName) {
                case 'object_id':
                    $item['contentId'] = $node->nodeValue;
                    break;
                case 'node_id':
                    $item['locationId'] = $node->nodeValue;
                    break;
                case 'priority':
                    $item[$node->nodeName] = $node->nodeValue;
                    break;
                case 'ts_publication':
                    $item['publicationDate'] = $node->nodeValue;
                    break;
                case 'ts_visible':
                    $item['visibilityDate'] = $node->nodeValue;
                    break;
                case 'ts_hidden':
                    $item['hiddenDate'] = $node->nodeValue;
                    break;
                case 'rotation_until':
                    $item['rotationUntilDate'] = $node->nodeValue;
                    break;
                case 'moved_to':
                    $item['movedTo'] = $node->nodeValue;
                    break;
            }
        }

        return new Parts\Item($item);
    }
}
