<?php

/**
 * File containing the RelationListTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\FieldType\RelationList\Type;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use PHPUnit_Framework_TestCase;

/**
 * Test case for RelationList converter in Legacy storage.
 */
class RelationListTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\RelationListConverter
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = $this
            ->getMockBuilder('eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\RelationListConverter')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelationXmlHashFromDB'))
            ->getMock();
    }

    /**
     * @group fieldType
     * @group relationlist
     */
    public function testToStorageValue()
    {
        $destinationContentIds = array(3, 2, 1);
        $fieldValue = new FieldValue();
        $fieldValue->sortKey = false;
        $fieldValue->data = array('destinationContentIds' => $destinationContentIds);

        $expectedStorageFieldValue = new StorageFieldValue();
        $expectedStorageFieldValue->dataText = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<related-objects><relation-list><relation-item priority="1" contentobject-id="3" contentobject-version="33" node-id="35" parent-node-id="36" contentclass-id="34" contentclass-identifier="37" contentobject-remote-id="32"/><relation-item priority="2" contentobject-id="2" contentobject-version="23" node-id="25" parent-node-id="26" contentclass-id="24" contentclass-identifier="27" contentobject-remote-id="22"/><relation-item priority="3" contentobject-id="1" contentobject-version="13" node-id="15" parent-node-id="16" contentclass-id="14" contentclass-identifier="17" contentobject-remote-id="12"/></relation-list></related-objects>

EOT;

        $actualStorageFieldValue = new StorageFieldValue();

        $this->converter
            ->expects($this->once())
            ->method('getRelationXmlHashFromDB')
            ->with($destinationContentIds)
            ->will(
                $this->returnValue(
                    array(
                        '1' => array(
                            array(
                                'ezcontentobject_remote_id' => '12',
                                'ezcontentobject_current_version' => '13',
                                'ezcontentobject_contentclass_id' => '14',
                                'ezcontentobject_tree_node_id' => '15',
                                'ezcontentobject_tree_parent_node_id' => '16',
                                'ezcontentclass_identifier' => '17',
                            ),
                        ),
                        '3' => array(
                            array(
                                'ezcontentobject_remote_id' => '32',
                                'ezcontentobject_current_version' => '33',
                                'ezcontentobject_contentclass_id' => '34',
                                'ezcontentobject_tree_node_id' => '35',
                                'ezcontentobject_tree_parent_node_id' => '36',
                                'ezcontentclass_identifier' => '37',
                            ),
                        ),
                        '2' => array(
                            array(
                                'ezcontentobject_remote_id' => '22',
                                'ezcontentobject_current_version' => '23',
                                'ezcontentobject_contentclass_id' => '24',
                                'ezcontentobject_tree_node_id' => '25',
                                'ezcontentobject_tree_parent_node_id' => '26',
                                'ezcontentclass_identifier' => '27',
                            ),
                        ),
                    )
                )
            );

        $this->converter->toStorageValue($fieldValue, $actualStorageFieldValue);

        $this->assertEquals(
            $expectedStorageFieldValue,
            $actualStorageFieldValue
        );
    }

    /**
     * @group fieldType
     * @group relationlist
     */
    public function testToStorageValueEmpty()
    {
        $destinationContentIds = array();
        $fieldValue = new FieldValue();
        $fieldValue->sortKey = false;
        $fieldValue->data = array('destinationContentIds' => $destinationContentIds);

        $expectedStorageFieldValue = new StorageFieldValue();
        $expectedStorageFieldValue->dataText = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<related-objects><relation-list/></related-objects>

EOT;

        $actualStorageFieldValue = new StorageFieldValue();

        $this->converter
            ->expects($this->once())
            ->method('getRelationXmlHashFromDB')
            ->with($destinationContentIds)
            ->will($this->returnValue(array()));

        $this->converter->toStorageValue($fieldValue, $actualStorageFieldValue);

        $this->assertEquals(
            $expectedStorageFieldValue,
            $actualStorageFieldValue
        );
    }

    /**
     * @group fieldType
     * @group relationlist
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->sortKeyString = '';
        $storageFieldValue->dataText = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<related-objects><relation-list><relation-item priority="2" contentobject-id="2" contentobject-version="23" node-id="25" parent-node-id="26" contentclass-id="24" contentclass-identifier="27" contentobject-remote-id="22"/><relation-item priority="3" contentobject-id="1" contentobject-version="13" node-id="15" parent-node-id="16" contentclass-id="14" contentclass-identifier="17" contentobject-remote-id="12"/><relation-item priority="1" contentobject-id="3" contentobject-version="33" node-id="35" parent-node-id="36" contentclass-id="34" contentclass-identifier="37" contentobject-remote-id="32"/></relation-list></related-objects>

EOT;

        $expectedFieldValue = new FieldValue();
        $expectedFieldValue->data = array('destinationContentIds' => array(3, 2, 1));

        $actualFieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $actualFieldValue);

        $this->assertEquals(
            $expectedFieldValue,
            $actualFieldValue
        );
    }

    /**
     * @group fieldType
     * @group relationlist
     */
    public function testToFieldValueEmpty()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->sortKeyString = '';
        $storageFieldValue->dataText = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<related-objects><relation-list/></related-objects>

EOT;

        $expectedFieldValue = new FieldValue();
        $expectedFieldValue->data = array('destinationContentIds' => array());

        $actualFieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $actualFieldValue);

        $this->assertEquals(
            $expectedFieldValue,
            $actualFieldValue
        );
    }

    /**
     * @group fieldType
     * @group relationlist
     */
    public function testToStorageFieldDefinition()
    {
        $fieldDefinition = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => new FieldTypeConstraints(
                    array(
                        'fieldSettings' => array(
                            'selectionMethod' => Type::SELECTION_BROWSE,
                            'selectionDefaultLocation' => 12345,
                            'selectionContentTypes' => array('article', 'blog_post'),
                        ),
                    )
                ),
            )
        );

        $expectedStorageFieldDefinition = new StorageFieldDefinition();
        $expectedStorageFieldDefinition->dataText5 = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<related-objects><constraints><allowed-class contentclass-identifier="article"/><allowed-class contentclass-identifier="blog_post"/></constraints><type value="2"/><object_class value=""/><selection_type value="0"/><contentobject-placement node-id="12345"/></related-objects>

EOT;

        $actualStorageFieldDefinition = new StorageFieldDefinition();

        $this->converter->toStorageFieldDefinition($fieldDefinition, $actualStorageFieldDefinition);

        $this->assertEquals(
            $expectedStorageFieldDefinition,
            $actualStorageFieldDefinition
        );
    }

    /**
     * @group fieldType
     * @group relationlist
     */
    public function testToFieldDefinitionMultiple()
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $storageFieldDefinition->dataText5 = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<related-objects>
    <constraints>
        <allowed-class contentclass-identifier="forum"/>
        <allowed-class contentclass-identifier="folder"/>
    </constraints><type value="2"/>
    <object_class value=""/>
    <selection_type value="1"/>
    <contentobject-placement node-id="54321"/>
</related-objects>

EOT;

        $expectedFieldDefinition = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => new FieldTypeConstraints(
                    array(
                        'fieldSettings' => array(
                            'selectionMethod' => Type::SELECTION_DROPDOWN,
                            'selectionDefaultLocation' => 54321,
                            'selectionContentTypes' => array('forum', 'folder'),
                        ),
                    )
                ),
                'defaultValue' => new FieldValue(
                    array(
                        'data' => array('destinationContentIds' => array()),
                    )
                ),
            )
        );

        $actualFieldDefinition = new PersistenceFieldDefinition();

        $this->converter->toFieldDefinition($storageFieldDefinition, $actualFieldDefinition);

        $this->assertEquals(
            $expectedFieldDefinition,
            $actualFieldDefinition
        );
    }
}
