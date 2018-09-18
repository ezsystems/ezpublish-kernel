<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\Gateway\DoctrineDatabaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase;
// For SORT_ORDER_* constants
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase.
 */
class DoctrineDatabaseTest extends LanguageAwareTestCase
{
    /**
     * The DoctrineDatabase gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase
     */
    protected $gateway;

    public function setUp()
    {
        parent::setUp();

        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/languages.php');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::__construct
     */
    public function testCtor()
    {
        $handlerMock = $this->getDatabaseHandler();
        $gateway = $this->getGateway();

        $this->assertAttributeSame(
            $handlerMock,
            'dbHandler',
            $gateway
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::insertGroup
     */
    public function testInsertGroup()
    {
        $gateway = $this->getGateway();

        $group = $this->getGroupFixture();

        $id = $gateway->insertGroup($group);

        $this->assertQueryResult(
            array(
                array(
                    'id' => '1',
                    'created' => '1032009743',
                    'creator_id' => '14',
                    'modified' => '1033922120',
                    'modifier_id' => '14',
                    'name' => 'Media',
                ),
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    'id',
                    'created',
                    'creator_id',
                    'modified',
                    'modifier_id',
                    'name'
                )
                ->from('ezcontentclassgroup')
        );
    }

    /**
     * Returns a Group fixture.
     *
     * @return Group
     */
    protected function getGroupFixture()
    {
        $group = new Group();

        $group->name = array(
            'always-available' => 'eng-GB',
            'eng-GB' => 'Media',
        );
        $group->description = array(
            'always-available' => 'eng-GB',
            'eng-GB' => '',
        );
        $group->identifier = 'Media';
        $group->created = 1032009743;
        $group->modified = 1033922120;
        $group->creatorId = 14;
        $group->modifierId = 14;

        return $group;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::updateGroup
     */
    public function testUpdateGroup()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_groups.php'
        );

        $gateway = $this->getGateway();

        $struct = $this->getGroupUpdateStructFixture();

        $res = $gateway->updateGroup($struct);

        $this->assertQueryResult(
            array(
                array('3'),
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select('COUNT(*)')
                ->from('ezcontentclassgroup')
        );

        $q = $this->getDatabaseHandler()->createSelectQuery();
        $q
            ->select(
                'id',
                'created',
                'creator_id',
                'modified',
                'modifier_id',
                'name'
            )
            ->from('ezcontentclassgroup')
            ->orderBy('id');
        $this->assertQueryResult(
            array(
                array(
                    'id' => 1,
                    'created' => 1031216928,
                    'creator_id' => 14,
                    'modified' => 1033922106,
                    'modifier_id' => 14,
                    'name' => 'Content',
                ),
                array(
                    'id' => 2,
                    'created' => 1031216941,
                    'creator_id' => 14,
                    'modified' => 1311454096,
                    'modifier_id' => 23,
                    'name' => 'UpdatedGroup',
                ),
                array(
                    'id' => 3,
                    'created' => 1032009743,
                    'creator_id' => 14,
                    'modified' => 1033922120,
                    'modifier_id' => 14,
                    'name' => 'Media',
                ),
            ),
            $q
        );
    }

    /**
     * Returns a Group update struct fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct
     */
    protected function getGroupUpdateStructFixture()
    {
        $struct = new GroupUpdateStruct();

        $struct->id = 2;
        $struct->name = array(
            'always-available' => 'eng-GB',
            'eng-GB' => 'UpdatedGroupName',
        );
        $struct->description = array(
            'always-available' => 'eng-GB',
            'eng-GB' => '',
        );
        $struct->identifier = 'UpdatedGroup';
        $struct->modified = 1311454096;
        $struct->modifierId = 23;

        return $struct;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::countTypesInGroup
     */
    public function testCountTypesInGroup()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();

        $this->assertEquals(
            3,
            $gateway->countTypesInGroup(1)
        );
        $this->assertEquals(
            0,
            $gateway->countTypesInGroup(23)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::countGroupsForType
     */
    public function testCountGroupsForType()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();

        $this->assertEquals(
            1,
            $gateway->countGroupsForType(1, 1)
        );
        $this->assertEquals(
            0,
            $gateway->countGroupsForType(23, 0)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::deleteGroup
     */
    public function testDeleteGroup()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_groups.php'
        );

        $gateway = $this->getGateway();

        $gateway->deleteGroup(2);

        $this->assertQueryResult(
            array(
                array('1'),
                array('3'),
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select('id')
                ->from('ezcontentclassgroup')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::loadGroupData
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::createGroupLoadQuery
     */
    public function testLoadGroupData()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_groups.php'
        );

        $gateway = $this->getGateway();
        $data = $gateway->loadGroupData([2]);

        $this->assertEquals(
            array(
                array(
                    'created' => '1031216941',
                    'creator_id' => '14',
                    'id' => '2',
                    'modified' => '1033922113',
                    'modifier_id' => '14',
                    'name' => 'Users',
                ),
            ),
            $data
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::loadGroupDataByIdentifier
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::createGroupLoadQuery
     */
    public function testLoadGroupDataByIdentifier()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_groups.php'
        );

        $gateway = $this->getGateway();
        $data = $gateway->loadGroupDataByIdentifier('Users');

        $this->assertEquals(
            array(
                array(
                    'created' => '1031216941',
                    'creator_id' => '14',
                    'id' => '2',
                    'modified' => '1033922113',
                    'modifier_id' => '14',
                    'name' => 'Users',
                ),
            ),
            $data
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::loadAllGroupsData
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::createGroupLoadQuery
     */
    public function testLoadAllGroupsData()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_groups.php'
        );

        $gateway = $this->getGateway();
        $data = $gateway->loadAllGroupsData();

        $this->assertEquals(
            3,
            count($data)
        );

        $this->assertEquals(
            array(
                'created' => '1031216941',
                'creator_id' => '14',
                'id' => '2',
                'modified' => '1033922113',
                'modifier_id' => '14',
                'name' => 'Users',
            ),
            $data[1]
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::loadTypesDataForGroup
     */
    public function testLoadTypesDataForGroup()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();
        $rows = $gateway->loadTypesDataForGroup(1, 0);

        $this->assertEquals(
            6,
            count($rows)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::loadTypeData
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::getLoadTypeQuery
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::selectColumns
     */
    public function testLoadTypeData()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();
        $rows = $gateway->loadTypeData(1, 0);

        $this->assertEquals(
            5,
            count($rows)
        );
        $this->assertEquals(
            43,
            count($rows[0])
        );

        /*
         * Store mapper fixture
         *
        file_put_contents(
            dirname( __DIR__ ) . '/_fixtures/map_load_type.php',
            "<?php\n\nreturn " . var_export( $rows, true ) . ";\n"
        );
         */
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::loadTypeDataByIdentifier
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::getLoadTypeQuery
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::selectColumns
     */
    public function testLoadTypeDataByIdentifier()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();
        $rows = $gateway->loadTypeDataByIdentifier('folder', 0);

        $this->assertEquals(
            5,
            count($rows)
        );
        $this->assertEquals(
            43,
            count($rows[0])
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::loadTypeDataByRemoteId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::getLoadTypeQuery
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::selectColumns
     */
    public function testLoadTypeDataByRemoteId()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();
        $rows = $gateway->loadTypeDataByRemoteId('a3d405b81be900468eb153d774f4f0d2', 0);

        $this->assertEquals(
            5,
            count($rows)
        );
        $this->assertEquals(
            43,
            count($rows[0])
        );
    }

    /**
     * Returns the expected data from creating a type.
     *
     * @return string[][]
     */
    public static function getTypeCreationExpectations()
    {
        return array(
            array('always_available', 0),
            array('contentobject_name', '<short_name|name>'),
            array('created', '1024392098'),
            array('creator_id', '14'),
            array('identifier', 'folder'),
            array('initial_language_id', '2'),
            array('is_container', '1'),
            array('language_mask', 7),
            array('modified', '1082454875'),
            array('modifier_id', '14'),
            array('remote_id', 'a3d405b81be900468eb153d774f4f0d2'),
            array('serialized_description_list', 'a:2:{i:0;s:0:"";s:16:"always-available";b:0;}'),
            array('serialized_name_list', 'a:3:{s:16:"always-available";s:6:"eng-US";s:6:"eng-US";s:6:"Folder";s:6:"eng-GB";s:11:"Folder (GB)";}'),
            array('sort_field', 7),
            array('sort_order', 1),
            array('url_alias_name', ''),
            array('version', '0'),
        );
    }

    /**
     * @dataProvider getTypeCreationExpectations
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::insertType
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::setCommonTypeColumns
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::insertTypeNameData
     */
    public function testInsertType($column, $expectation)
    {
        $gateway = $this->getGateway();
        $type = $this->getTypeFixture();

        $gateway->insertType($type);

        $this->assertQueryResult(
            array(array($expectation)),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select($column)
                ->from('ezcontentclass'),
            'Inserted Type data incorrect in column ' . $column
        );
    }

    /**
     * Returns the data expected to be inserted in ezcontentclass_name.
     *
     * @return string[][]
     */
    public static function getTypeCreationContentClassNameExpectations()
    {
        return array(
            array('contentclass_id', array(1, 1)),
            array('contentclass_version', array(0, 0)),
            array('language_id', array(3, 4)),
            array('language_locale', array('eng-US', 'eng-GB')),
            array('name', array('Folder', 'Folder (GB)')),
        );
    }

    /**
     * @dataProvider getTypeCreationContentClassNameExpectations
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::insertType
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::setCommonTypeColumns
     */
    public function testInsertTypeContentClassName($column, $expectation)
    {
        $gateway = $this->getGateway();
        $type = $this->getTypeFixture();

        $gateway->insertType($type);

        $this->assertQueryResult(
            array_map(
                function ($value) {
                    return array($value);
                },
                $expectation
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select($column)
                ->from('ezcontentclass_name'),
            'Inserted Type data incorrect in column ' . $column
        );
    }

    /**
     * Returns a Type fixture.
     *
     * @return Type
     */
    protected function getTypeFixture()
    {
        $type = new Type();

        $type->status = 0;
        $type->name = array(
            'always-available' => 'eng-US',
            'eng-US' => 'Folder',
            'eng-GB' => 'Folder (GB)',
        );
        $type->description = array(
            0 => '',
            'always-available' => false,
        );
        $type->identifier = 'folder';
        $type->created = 1024392098;
        $type->modified = 1082454875;
        $type->creatorId = 14;
        $type->modifierId = 14;
        $type->remoteId = 'a3d405b81be900468eb153d774f4f0d2';
        $type->urlAliasSchema = '';
        $type->nameSchema = '<short_name|name>';
        $type->isContainer = true;
        $type->initialLanguageId = 2;
        $type->sortField = Location::SORT_FIELD_CLASS_NAME;
        $type->sortOrder = Location::SORT_ORDER_ASC;

        return $type;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::insertFieldDefinition
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::setCommonFieldColumns
     */
    public function testInsertFieldDefinition()
    {
        $gateway = $this->getGateway();

        $field = $this->getFieldDefinitionFixture();
        $storageField = $this->getStorageFieldDefinitionFixture();

        $gateway->insertFieldDefinition(23, 1, $field, $storageField);

        $this->assertQueryResult(
            array(
                array(
                    'contentclass_id' => '23',
                    'serialized_name_list' => 'a:2:{s:16:"always-available";s:6:"eng-US";s:6:"eng-US";s:11:"Description";}',
                    'serialized_description_list' => 'a:2:{s:16:"always-available";s:6:"eng-GB";s:6:"eng-GB";s:16:"Some description";}',
                    'identifier' => 'description',
                    'category' => 'meta',
                    'placement' => '4',
                    'data_type_string' => 'ezrichtext',
                    'can_translate' => '1',
                    'is_required' => '1',
                    'is_information_collector' => '1',
                    'serialized_data_text' => 'a:2:{i:0;s:0:"";s:16:"always-available";b:0;}',
                    'version' => '1',

                    'data_float1' => '0.1',
                    'data_float2' => '0.2',
                    'data_float3' => '0.3',
                    'data_float4' => '0.4',
                    'data_int1' => '1',
                    'data_int2' => '2',
                    'data_int3' => '3',
                    'data_int4' => '4',
                    'data_text1' => 'a',
                    'data_text2' => 'b',
                    'data_text3' => 'c',
                    'data_text4' => 'd',
                    'data_text5' => 'e',
                    'serialized_data_text' => 'a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}',
                ),
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    'contentclass_id',
                    'serialized_name_list',
                    'serialized_description_list',
                    'identifier',
                    'category',
                    'placement',
                    'data_type_string',
                    'can_translate',
                    'is_required',
                    'is_information_collector',
                    'serialized_data_text',
                    'version',
                    'data_float1',
                    'data_float2',
                    'data_float3',
                    'data_float4',
                    'data_int1',
                    'data_int2',
                    'data_int3',
                    'data_int4',
                    'data_text1',
                    'data_text2',
                    'data_text3',
                    'data_text4',
                    'data_text5',
                    'serialized_data_text'
                )
                ->from('ezcontentclass_attribute'),
            'FieldDefinition not inserted correctly'
        );
    }

    /**
     * Returns a FieldDefinition fixture.
     *
     * @return FieldDefinition
     */
    protected function getFieldDefinitionFixture()
    {
        $field = new FieldDefinition();

        $field->name = array(
            'always-available' => 'eng-US',
            'eng-US' => 'Description',
        );
        $field->description = array(
            'always-available' => 'eng-GB',
            'eng-GB' => 'Some description',
        );
        $field->identifier = 'description';
        $field->fieldGroup = 'meta';
        $field->position = 4;
        $field->fieldType = 'ezrichtext';
        $field->isTranslatable = true;
        $field->isRequired = true;
        $field->isInfoCollector = true;
        // $field->fieldTypeConstraints ???
        $field->defaultValue = array(
            0 => '',
            'always-available' => false,
        );

        return $field;
    }

    /**
     * Returns a StorageFieldDefinition fixture.
     *
     * @return StorageFieldDefinition
     */
    protected function getStorageFieldDefinitionFixture()
    {
        $fieldDef = new StorageFieldDefinition();

        $fieldDef->dataFloat1 = 0.1;
        $fieldDef->dataFloat2 = 0.2;
        $fieldDef->dataFloat3 = 0.3;
        $fieldDef->dataFloat4 = 0.4;

        $fieldDef->dataInt1 = 1;
        $fieldDef->dataInt2 = 2;
        $fieldDef->dataInt3 = 3;
        $fieldDef->dataInt4 = 4;

        $fieldDef->dataText1 = 'a';
        $fieldDef->dataText2 = 'b';
        $fieldDef->dataText3 = 'c';
        $fieldDef->dataText4 = 'd';
        $fieldDef->dataText5 = 'e';

        $fieldDef->serializedDataText = array(
            'foo', 'bar',
        );

        return $fieldDef;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::deleteFieldDefinition
     */
    public function testDeleteFieldDefinition()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();

        $gateway->deleteFieldDefinition(1, 0, 119);

        $this->assertQueryResult(
            array(array(6)),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select('COUNT(*)')
                ->from('ezcontentclass_attribute')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::updateFieldDefinition
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::setCommonFieldColumns
     */
    public function testUpdateFieldDefinition()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );
        $fieldDefinitionFixture = $this->getFieldDefinitionFixture();
        $fieldDefinitionFixture->id = 160;
        $storageFieldDefinitionFixture = $this->getStorageFieldDefinitionFixture();

        $gateway = $this->getGateway();
        $gateway->updateFieldDefinition(2, 0, $fieldDefinitionFixture, $storageFieldDefinitionFixture);

        $this->assertQueryResult(
            array(
                // "random" sample
                array(
                    'category' => 'meta',
                    'contentclass_id' => '2',
                    'version' => '0',
                    'data_type_string' => 'ezrichtext',
                    'identifier' => 'description',
                    'is_information_collector' => '1',
                    'placement' => '4',
                    'serialized_description_list' => 'a:2:{s:16:"always-available";s:6:"eng-GB";s:6:"eng-GB";s:16:"Some description";}',

                    'data_float1' => '0.1',
                    'data_float2' => '0.2',
                    'data_float3' => '0.3',
                    'data_float4' => '0.4',
                    'data_int1' => '1',
                    'data_int2' => '2',
                    'data_int3' => '3',
                    'data_int4' => '4',
                    'data_text1' => 'a',
                    'data_text2' => 'b',
                    'data_text3' => 'c',
                    'data_text4' => 'd',
                    'data_text5' => 'e',
                    'serialized_data_text' => 'a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}',
                ),
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    'category',
                    'contentclass_id',
                    'version',
                    'data_type_string',
                    'identifier',
                    'is_information_collector',
                    'placement',
                    'serialized_description_list',
                    'data_float1',
                    'data_float2',
                    'data_float3',
                    'data_float4',
                    'data_int1',
                    'data_int2',
                    'data_int3',
                    'data_int4',
                    'data_text1',
                    'data_text2',
                    'data_text3',
                    'data_text4',
                    'data_text5',
                    'serialized_data_text'
                )
                ->from('ezcontentclass_attribute')
                ->where('id = 160'),
            'FieldDefinition not updated correctly'
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::insertGroupAssignment
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::loadGroupData
     */
    public function testInsertGroupAssignment()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_groups.php'
        );

        $gateway = $this->getGateway();

        $gateway->insertGroupAssignment(3, 42, 1);

        $this->assertQueryResult(
            array(
                array(
                    'contentclass_id' => '42',
                    'contentclass_version' => '1',
                    'group_id' => '3',
                    'group_name' => 'Media',
                ),
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    'contentclass_id',
                    'contentclass_version',
                    'group_id',
                    'group_name'
                )->from('ezcontentclass_classgroup')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::deleteGroupAssignment
     */
    public function testDeleteGroupAssignment()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();

        $gateway->deleteGroupAssignment(1, 1, 0);

        $this->assertQueryResult(
            array(array('1')),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    'COUNT(*)'
                )->from('ezcontentclass_classgroup')
                ->where('contentclass_id = 1')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::updateType
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::setCommonTypeColumns
     * @dataProvider getTypeUpdateExpectations
     */
    public function testUpdateType($fieldName, $expectedValue)
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();

        $updateStruct = $this->getTypeUpdateFixture();

        $gateway->updateType(1, 0, $updateStruct);

        $this->assertQueryResult(
            array(
                array(
                    $fieldName => $expectedValue,
                ),
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    $fieldName
                )->from('ezcontentclass')
                ->where('id = 1 AND version = 0'),
            "Incorrect value stored for '{$fieldName}'."
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::deleteTypeNameData
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::insertTypeNameData
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::updateType
     */
    public function testUpdateTypeName()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();

        $updateStruct = $this->getTypeUpdateFixture();

        $gateway->updateType(1, 0, $updateStruct);

        $this->assertQueryResult(
            array(
                array(
                    'contentclass_id' => 1,
                    'contentclass_version' => 0,
                    'language_id' => 3,
                    'language_locale' => 'eng-US',
                    'name' => 'New Folder',
                ),
                array(
                    'contentclass_id' => 1,
                    'contentclass_version' => 0,
                    'language_id' => 4,
                    'language_locale' => 'eng-GB',
                    'name' => 'New Folder for you',
                ),
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select('*')
                ->from('ezcontentclass_name')
                ->where('contentclass_id = 1 AND contentclass_version = 0')
        );
    }

    /**
     * Returns expected data after update.
     *
     * Data provider for {@link testUpdateType()}.
     *
     * @return string[][]
     */
    public static function getTypeUpdateExpectations()
    {
        return array(
            array('serialized_name_list', 'a:3:{s:16:"always-available";s:6:"eng-US";s:6:"eng-US";s:10:"New Folder";s:6:"eng-GB";s:18:"New Folder for you";}'),
            array('serialized_description_list', 'a:2:{i:0;s:0:"";s:16:"always-available";b:0;}'),
            array('identifier', 'new_folder'),
            array('modified', '1311621548'),
            array('modifier_id', '42'),
            array('remote_id', 'foobar'),
            array('url_alias_name', 'some scheke'),
            array('contentobject_name', '<short_name>'),
            array('is_container', '0'),
            array('initial_language_id', '23'),
            array('sort_field', '3'),
            array('sort_order', '0'),
            array('always_available', '1'),
        );
    }

    /**
     * Returns a eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct
     */
    protected function getTypeUpdateFixture()
    {
        $struct = new UpdateStruct();

        $struct->name = array(
            'always-available' => 'eng-US',
            'eng-US' => 'New Folder',
            'eng-GB' => 'New Folder for you',
        );
        $struct->description = array(
            0 => '',
            'always-available' => false,
        );
        $struct->identifier = 'new_folder';
        $struct->modified = 1311621548;
        $struct->modifierId = 42;
        $struct->remoteId = 'foobar';
        $struct->urlAliasSchema = 'some scheke';
        $struct->nameSchema = '<short_name>';
        $struct->isContainer = false;
        $struct->initialLanguageId = 23;
        $struct->sortField = 3;
        $struct->sortOrder = Location::SORT_ORDER_DESC;
        $struct->defaultAlwaysAvailable = true;

        return $struct;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::countInstancesOfType
     */
    public function testCountInstancesOfTypeExist()
    {
        $this->insertDatabaseFixture(
            // Fixture for content objects
            __DIR__ . '/../../_fixtures/contentobjects.php'
        );

        $gateway = $this->getGateway();
        $res = $gateway->countInstancesOfType(3, 0);

        $this->assertEquals(
            6,
            $res
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::countInstancesOfType
     */
    public function testCountInstancesOfTypeNotExist()
    {
        $this->insertDatabaseFixture(
            // Fixture for content objects
            __DIR__ . '/../../_fixtures/contentobjects.php'
        );

        $gateway = $this->getGateway();
        $res = $gateway->countInstancesOfType(23422342, 1);

        $this->assertEquals(
            0,
            $res
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::deleteFieldDefinitionsForType
     */
    public function testDeleteFieldDefinitionsForTypeExisting()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();

        $gateway->deleteFieldDefinitionsForType(1, 0);

        $countAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countAffectedAttr
            ->select('COUNT(*)')
            ->from('ezcontentclass_attribute')
            ->where(
                $countAffectedAttr->expr->eq(
                    'contentclass_id',
                    1
                )
            );
        // 1 left with version 1
        $this->assertQueryResult(
            array(array(1)),
            $countAffectedAttr
        );

        $countNotAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countNotAffectedAttr->select('COUNT(*)')
            ->from('ezcontentclass_attribute');

        $this->assertQueryResult(
            array(array(2)),
            $countNotAffectedAttr
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::deleteFieldDefinitionsForType
     */
    public function testDeleteFieldDefinitionsForTypeNotExisting()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();

        $gateway->deleteFieldDefinitionsForType(23, 1);

        $countNotAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countNotAffectedAttr->select('COUNT(*)')
            ->from('ezcontentclass_attribute');

        $this->assertQueryResult(
            array(array(7)),
            $countNotAffectedAttr
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::deleteGroupAssignmentsForType
     */
    public function testDeleteGroupAssignmentsForTypeExisting()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();

        $gateway->deleteGroupAssignmentsForType(1, 0);

        $countAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countAffectedAttr->select('COUNT(*)')
            ->from('ezcontentclass_classgroup');

        $this->assertQueryResult(
            array(array(2)),
            $countAffectedAttr
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::deleteGroupAssignmentsForType
     */
    public function testDeleteGroupAssignmentsForTypeNotExisting()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();

        $gateway->deleteType(23, 1);

        $countAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countAffectedAttr->select('COUNT(*)')
            ->from('ezcontentclass_classgroup');

        $this->assertQueryResult(
            array(array(3)),
            $countAffectedAttr
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::deleteType
     */
    public function testDeleteTypeExisting()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();

        $gateway->deleteType(1, 0);

        $countAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countAffectedAttr->select('COUNT(*)')
            ->from('ezcontentclass');

        $this->assertQueryResult(
            array(array(1)),
            $countAffectedAttr
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::deleteType
     */
    public function testDeleteTypeNotExisting()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = $this->getGateway();

        $gateway->deleteType(23, 1);

        $countAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countAffectedAttr->select('COUNT(*)')
            ->from('ezcontentclass');

        $this->assertQueryResult(
            array(array(2)),
            $countAffectedAttr
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase::publishTypeAndFields
     */
    public function testPublishTypeAndFields()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/type_to_publish.php'
        );

        $gateway = $this->getGateway();
        $gateway->publishTypeAndFields(1, 1, 0);

        $this->assertQueryResult(
            array(array(1)),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select('COUNT( * )')
                ->from('ezcontentclass')
                ->where('id = 1 AND version = 0')
        );

        $this->assertQueryResult(
            array(array(2)),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select('COUNT( * )')
                ->from('ezcontentclass_classgroup')
                ->where('contentclass_id = 1 AND contentclass_version = 0')
        );

        $this->assertQueryResult(
            array(array(5)),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select('COUNT( * )')
                ->from('ezcontentclass_attribute')
                ->where('contentclass_id = 1 AND version = 0')
        );

        $this->assertQueryResult(
            array(array(1)),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select('COUNT( * )')
                ->from('ezcontentclass_name')
                ->where('contentclass_id = 1 AND contentclass_version = 0')
        );
    }

    /**
     * Returns the DoctrineDatabase gateway to test.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase
     */
    protected function getGateway()
    {
        if (!isset($this->gateway)) {
            $this->gateway = new DoctrineDatabase(
                $this->getDatabaseHandler(),
                $this->getDatabaseConnection(),
                $this->getLanguageMaskGenerator()
            );
        }

        return $this->gateway;
    }
}
