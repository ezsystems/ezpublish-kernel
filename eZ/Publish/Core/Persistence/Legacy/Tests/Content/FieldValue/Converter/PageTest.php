<?php

/**
 * File containing the PageTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\PageConverter;
use eZ\Publish\Core\FieldType\Page\Parts;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use PHPUnit_Framework_TestCase;

class PageTest extends PHPUnit_Framework_TestCase
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

    /**
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Page
     */
    private $pageReference;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\PageConverter
     */
    private $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new PageConverter();

        $this->pageReference = new Parts\Page(
            array(
                'layout' => '2ZonesLayout1',
                'zones' => array(
                    new Parts\Zone(
                        array(
                            'id' => '6c7f907b831a819ed8562e3ddce5b264',
                            'identifier' => 'left',
                            'blocks' => array(
                                new Parts\Block(
                                    array(
                                        'id' => '1e1e355c8da3c92e80354f243c6dd37b',
                                        'name' => 'Campaign',
                                        'type' => 'Campaign',
                                        'view' => 'default',
                                        'overflowId' => '',
                                        'zoneId' => '6c7f907b831a819ed8562e3ddce5b264',
                                        'attributes' => array(),
                                    )
                                ),
                                new Parts\Block(
                                    array(
                                        'id' => '250bcab3ea2929edbf72ece096dcdb7a',
                                        'name' => 'Amazon Gallery',
                                        'type' => 'Gallery',
                                        'view' => 'default',
                                        'overflowId' => '',
                                        'zoneId' => '6c7f907b831a819ed8562e3ddce5b264',
                                        'items' => array(
                                            new Parts\Item(
                                                array(
                                                    'action' => 'add',
                                                    'contentId' => '62',
                                                    'locationId' => '64',
                                                    'priority' => '1',
                                                    'publicationDate' => '1393607060',
                                                    'visibilityDate' => '1393607060',
                                                    'hiddenDate' => '1393607060',
                                                    'rotationUntilDate' => '1393607060',
                                                    'movedTo' => '42',
                                                    'attributes' => array(),
                                                )
                                            ),
                                            new Parts\Item(
                                                array(
                                                    'action' => 'add',
                                                    'attributes' => array(),
                                                )
                                            ),
                                        ),
                                        'attributes' => array(),
                                    )
                                ),
                            ),
                        )
                    ),
                    new Parts\Zone(
                        array(
                            'id' => '656b2182b4be70f18ca7b44b3fbb6dbe',
                            'identifier' => 'right',
                            'blocks' => array(
                                new Parts\Block(
                                    array(
                                        'id' => '4d2f5e57d2a2528b276cd9e776a62e42',
                                        'name' => 'Featured Video',
                                        'type' => 'Video',
                                        'view' => 'default',
                                        'overflowId' => '',
                                        'zoneId' => '656b2182b4be70f18ca7b44b3fbb6dbe',
                                        'attributes' => array(),
                                    )
                                ),
                                new Parts\Block(
                                    array(
                                        'id' => 'f36743396b8c36f10b467aa52f133e58',
                                        'name' => 'Travel Information',
                                        'type' => 'ContentGrid',
                                        'view' => 'default',
                                        'overflowId' => '',
                                        'zoneId' => '656b2182b4be70f18ca7b44b3fbb6dbe',
                                        'attributes' => array(),
                                    )
                                ),
                            ),
                        )
                    ),
                ),
                'attributes' => array(),
            )
        );
    }

    /**
     * Test converting XML to Parts\Page.
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue(array('dataText' => self::PAGE_XML_REFERENCE));
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
        $fieldValue = new FieldValue(array('data' => $this->pageReference));
        $this->converter->toStorageValue($fieldValue, $storageFieldValue);
        $this->assertXmlStringEqualsXmlString(self::PAGE_XML_REFERENCE, $storageFieldValue->dataText);
    }
}
