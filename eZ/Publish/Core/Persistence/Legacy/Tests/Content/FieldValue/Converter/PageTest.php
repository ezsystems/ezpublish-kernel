<?php
/**
 * File containing the PageTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Page as PageConverter;
use eZ\Publish\Core\FieldType\Page\Parts;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use SimpleXMLElement;
use DOMDocument;

class PageTest extends \PHPUnit_Framework_TestCase
{
    const PAGE_XML_REFERENCE = <<<EOT
<?xml version="1.0"?>
<page>
  <zone_layout>2ZonesLayout1</zone_layout>
  <zone id="id_6c7f907b831a819ed8562e3ddce5b264">
    <zone_identifier>left</zone_identifier>
    <block id="id_1e1e355c8da3c92e80354f243c6dd37b">
      <name>Campaign</name>
      <zone_id>6c7f907b831a819ed8562e3ddce5b264</zone_id>
      <type>Campaign</type>
      <view>default</view>
      <overflow_id></overflow_id>
    </block>
    <block id="id_250bcab3ea2929edbf72ece096dcdb7a">
      <name>Amazon Gallery</name>
      <zone_id>6c7f907b831a819ed8562e3ddce5b264</zone_id>
      <type>Gallery</type>
      <view>default</view>
      <overflow_id></overflow_id>
    </block>
  </zone>
  <zone id="id_656b2182b4be70f18ca7b44b3fbb6dbe">
    <zone_identifier>right</zone_identifier>
    <block id="id_4d2f5e57d2a2528b276cd9e776a62e42">
      <name>Featured Video</name>
      <zone_id>656b2182b4be70f18ca7b44b3fbb6dbe</zone_id>
      <type>Video</type>
      <view>default</view>
      <overflow_id></overflow_id>
    </block>
    <block id="id_f36743396b8c36f10b467aa52f133e58">
      <name>Travel Information</name>
      <zone_id>656b2182b4be70f18ca7b44b3fbb6dbe</zone_id>
      <type>ContentGrid</type>
      <view>default</view>
      <overflow_id></overflow_id>
    </block>
  </zone>
</page>
EOT;

    /**
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Page
     */
    private $pageReference;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Page
     */
    private $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new PageConverter();

        $this->pageReference = new Parts\Page(
            array(
                'layout'    => '2ZonesLayout1',
                'zones'     => array(
                    new Parts\Zone(
                        array(
                            'id'            => '6c7f907b831a819ed8562e3ddce5b264',
                            'identifier'    => 'left',
                            'blocks'        => array(
                                new Parts\Block(
                                    array(
                                        'id'           => '1e1e355c8da3c92e80354f243c6dd37b',
                                        'name'         => 'Campaign',
                                        'type'         => 'Campaign',
                                        'view'         => 'default',
                                        'overflowId'   => '',
                                        'zoneId'       => '6c7f907b831a819ed8562e3ddce5b264',
                                        'attributes'    => array()
                                    )
                                ),
                                new Parts\Block(
                                    array(
                                        'id'            => '250bcab3ea2929edbf72ece096dcdb7a',
                                        'name'          => 'Amazon Gallery',
                                        'type'          => 'Gallery',
                                        'view'          => 'default',
                                        'overflowId'    => '',
                                        'zoneId'       => '6c7f907b831a819ed8562e3ddce5b264',
                                        'attributes'    => array()
                                    )
                                )
                            )
                        )
                    ),
                    new Parts\Zone(
                        array(
                            'id'            => '656b2182b4be70f18ca7b44b3fbb6dbe',
                            'identifier'    => 'right',
                            'blocks'        => array(
                                new Parts\Block(
                                    array(
                                        'id'            => '4d2f5e57d2a2528b276cd9e776a62e42',
                                        'name'          => 'Featured Video',
                                        'type'          => 'Video',
                                        'view'          => 'default',
                                        'overflowId'    => '',
                                        'zoneId'       => '656b2182b4be70f18ca7b44b3fbb6dbe',
                                        'attributes'    => array()
                                    )
                                ),
                                new Parts\Block(
                                    array(
                                        'id'            => 'f36743396b8c36f10b467aa52f133e58',
                                        'name'          => 'Travel Information',
                                        'type'          => 'ContentGrid',
                                        'view'          => 'default',
                                        'overflowId'    => '',
                                        'zoneId'       => '656b2182b4be70f18ca7b44b3fbb6dbe',
                                        'attributes'    => array()
                                    )
                                )
                            )
                        )
                    )
                ),
                'attributes'    => array()
            )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Page::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue( array( 'dataText' => self::PAGE_XML_REFERENCE ) );
        $fieldValue = new FieldValue;
        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Page', $fieldValue->data );
        $this->assertEquals( $this->pageReference, $fieldValue->data );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Page::toStorageValue
     */
    public function testToStorageValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $fieldValue = new FieldValue( array( 'data' => $this->pageReference ) );
        $this->converter->toStorageValue( $fieldValue, $storageFieldValue );
        $xml = new SimpleXMLElement( $storageFieldValue->dataText );

        foreach ( $xml as $nodeName => $node )
        {
            switch ( $nodeName )
            {
                case 'zone_layout':
                    $this->assertSame( $this->pageReference->layout, (string)$node );
                    break;
                case 'zone':
                    $this->checkZoneElement( $node );
                    break;
                default:
                    $this->assertSame( $this->pageReference->attributes[$nodeName], (string)$node );
            }
        }
    }

    private function checkZoneElement( SimpleXMLElement $zoneNode )
    {
        // id attribute starts with "id_", so extracting the rest to get the real id.
        $zoneId = substr( (string)$zoneNode['id'], 3 );

        // Check zone validity through extracted $zoneId
        $this->assertTrue( isset( $this->pageReference->zonesById[$zoneId] ) );
        $zone = $this->pageReference->zonesById[$zoneId];
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Zone', $zone );
        $this->assertSame( $zone->id, $zoneId );

        // Check zone properties validity
        foreach ( $zoneNode as $nodeName => $node )
        {
            switch ( $nodeName )
            {
                case 'zone_identifier':
                    $this->assertSame( $zone->identifier, (string)$node );
                    break;
                case 'block':
                    $this->checkBlockElement( $node, $zoneId );
                    break;
                default:
                    $this->assertSame( $zone->$nodeName, (string)$node );
            }
        }
    }

    private function checkBlockElement( SimpleXMLElement $blockNode, $currentZoneId )
    {
        // id attribute starts with "id_", so extracting the rest to get the real id.
        $blockId = substr( (string)$blockNode['id'], 3 );

        // Check zone validity through extracted $zoneId
        $this->assertTrue( isset( $this->pageReference->zonesById[$currentZoneId]->blocksById[$blockId] ) );
        $block = $this->pageReference->zonesById[$currentZoneId]->blocksById[$blockId];
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Block', $block );
        $this->assertSame( $block->id, $blockId );

        // Check zone properties validity
        foreach ( $blockNode as $nodeName => $node )
        {
            switch ( $nodeName )
            {
                case 'zone_id':
                    $this->assertSame( $block->zoneId, (string)$node );
                    break;
                case 'name':
                case 'type':
                case 'view':
                    $this->assertSame( $block->$nodeName, (string)$node );
                    break;
                case 'overflow_id':
                    $this->assertSame( $block->overflowId, (string)$node );
                    break;
                case 'custom_attributes':
                    foreach ( $node as $subNodeName => $subNode )
                    {
                        $this->assertSame( $block->customAttributes[$subNodeName], (string)$subNode );
                    }
                    break;
                case 'rotation':
                    foreach ( $node as $subNodeName => $subNode )
                    {
                        $this->assertSame( $block->rotation[$subNodeName], (string)$subNode );
                    }
                    break;
                default:
                    $this->assertSame( $block->$nodeName, (string)$node );
            }
        }
    }
}
