<?php

/**
 * File containing the PageTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\PageConverter;
use eZ\Publish\Core\FieldType\Page\Parts;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    const PAGE_XML_REFERENCE = <<<EOT
<?xml version="1.0"?>
<page>
  <zone id="id_6c7f907b831a819ed8562e3ddce5b264">
    <block id="id_1e1e355c8da3c92e80354f243c6dd37b">
      <name>Campaign</name>
      <type>Campaign</type>
      <view>default</view>
      <overflow_id></overflow_id>
      <zone_id>6c7f907b831a819ed8562e3ddce5b264</zone_id>
    </block>
    <block id="id_250bcab3ea2929edbf72ece096dcdb7a">
      <name>Amazon Gallery</name>
      <type>Gallery</type>
      <view>default</view>
      <overflow_id></overflow_id>
      <zone_id>6c7f907b831a819ed8562e3ddce5b264</zone_id>
      <item action="add">
        <object_id>62</object_id>
        <node_id>64</node_id>
        <priority>1</priority>
        <ts_publication>1393607060</ts_publication>
        <ts_visible>1393607060</ts_visible>
        <ts_hidden>1393607060</ts_hidden>
        <rotation_until>1393607060</rotation_until>
        <moved_to>42</moved_to>
      </item>
      <item action="add" />
    </block>
    <zone_identifier>left</zone_identifier>
  </zone>
  <zone id="id_656b2182b4be70f18ca7b44b3fbb6dbe">
    <block id="id_4d2f5e57d2a2528b276cd9e776a62e42">
      <name>Featured Video</name>
      <type>Video</type>
      <view>default</view>
      <overflow_id></overflow_id>
      <zone_id>656b2182b4be70f18ca7b44b3fbb6dbe</zone_id>
    </block>
    <block id="id_f36743396b8c36f10b467aa52f133e58">
      <name>Travel Information</name>
      <type>ContentGrid</type>
      <view>default</view>
      <overflow_id></overflow_id>
      <zone_id>656b2182b4be70f18ca7b44b3fbb6dbe</zone_id>
    </block>
    <zone_identifier>right</zone_identifier>
  </zone>
  <zone_layout>2ZonesLayout1</zone_layout>
</page>
EOT;

    /** @var \eZ\Publish\Core\FieldType\Page\Parts\Page */
    private $pageReference;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\PageConverter */
    private $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new PageConverter();

        $this->pageReference = new Parts\Page(
            [
                'layout' => '2ZonesLayout1',
                'zones' => [
                    new Parts\Zone(
                        [
                            'id' => '6c7f907b831a819ed8562e3ddce5b264',
                            'identifier' => 'left',
                            'blocks' => [
                                new Parts\Block(
                                    [
                                        'id' => '1e1e355c8da3c92e80354f243c6dd37b',
                                        'name' => 'Campaign',
                                        'type' => 'Campaign',
                                        'view' => 'default',
                                        'overflowId' => '',
                                        'zoneId' => '6c7f907b831a819ed8562e3ddce5b264',
                                        'attributes' => [],
                                    ]
                                ),
                                new Parts\Block(
                                    [
                                        'id' => '250bcab3ea2929edbf72ece096dcdb7a',
                                        'name' => 'Amazon Gallery',
                                        'type' => 'Gallery',
                                        'view' => 'default',
                                        'overflowId' => '',
                                        'zoneId' => '6c7f907b831a819ed8562e3ddce5b264',
                                        'items' => [
                                            new Parts\Item(
                                                [
                                                    'action' => 'add',
                                                    'contentId' => '62',
                                                    'locationId' => '64',
                                                    'priority' => '1',
                                                    'publicationDate' => '1393607060',
                                                    'visibilityDate' => '1393607060',
                                                    'hiddenDate' => '1393607060',
                                                    'rotationUntilDate' => '1393607060',
                                                    'movedTo' => '42',
                                                    'attributes' => [],
                                                ]
                                            ),
                                            new Parts\Item(
                                                [
                                                    'action' => 'add',
                                                    'attributes' => [],
                                                ]
                                            ),
                                        ],
                                        'attributes' => [],
                                    ]
                                ),
                            ],
                        ]
                    ),
                    new Parts\Zone(
                        [
                            'id' => '656b2182b4be70f18ca7b44b3fbb6dbe',
                            'identifier' => 'right',
                            'blocks' => [
                                new Parts\Block(
                                    [
                                        'id' => '4d2f5e57d2a2528b276cd9e776a62e42',
                                        'name' => 'Featured Video',
                                        'type' => 'Video',
                                        'view' => 'default',
                                        'overflowId' => '',
                                        'zoneId' => '656b2182b4be70f18ca7b44b3fbb6dbe',
                                        'attributes' => [],
                                    ]
                                ),
                                new Parts\Block(
                                    [
                                        'id' => 'f36743396b8c36f10b467aa52f133e58',
                                        'name' => 'Travel Information',
                                        'type' => 'ContentGrid',
                                        'view' => 'default',
                                        'overflowId' => '',
                                        'zoneId' => '656b2182b4be70f18ca7b44b3fbb6dbe',
                                        'attributes' => [],
                                    ]
                                ),
                            ],
                        ]
                    ),
                ],
                'attributes' => [],
            ]
        );
    }

    /**
     * Test converting XML to Parts\Page.
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue(['dataText' => self::PAGE_XML_REFERENCE]);
        $fieldValue = new FieldValue();
        $this->converter->toFieldValue($storageFieldValue, $fieldValue);
        $this->assertInstanceOf('eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Page', $fieldValue->data);
        $this->assertEquals($this->pageReference, $fieldValue->data);
    }

    /**
     * Test converting Parts\Page to XML.
     */
    public function testToStorageValue()
    {
        $storageFieldValue = new StorageFieldValue();
        $fieldValue = new FieldValue(['data' => $this->pageReference]);
        $this->converter->toStorageValue($fieldValue, $storageFieldValue);
        $this->assertXmlStringEqualsXmlString(self::PAGE_XML_REFERENCE, $storageFieldValue->dataText);
    }

    /**
     * Test converting from XML to storage and back.
     */
    public function testFromStorageAndBack()
    {
        $xml = <<<EOF
<?xml version="1.0"?>
<page>
  <zone id="id_ee94402090bb170600a8dab9e1bd1c5a">
    <block id="id_ef9fee870c65c676b2f7136431b73f37">
      <name>My Block</name>
      <type>my_block_type</type>
      <view>default</view>
      <overflow_id></overflow_id>
      <custom_attributes>
        <copytext></copytext>
      </custom_attributes>
      <rotation>
        <interval>0</interval>
        <type>0</type>
        <value></value>
        <unit></unit>
      </rotation>
      <zone_id>ee94402090bb170600a8dab9e1bd1c5a</zone_id>
    </block>
    <zone_identifier>zone_1</zone_identifier>
  </zone>
  <zone_layout>my_zone_layout</zone_layout>
</page>

EOF;

        $page = $this->converter->restoreValueFromXmlString($xml);
        $newXml = $this->converter->generateXmlString($page);
        $this->assertEquals($xml, $newXml);
    }
}
