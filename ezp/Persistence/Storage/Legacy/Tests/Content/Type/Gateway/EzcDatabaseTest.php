<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Type\Gateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Type\Gateway;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Tests\Content\Type\Gateway,
    ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase,

    ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\UpdateStruct,
    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;

/**
 * Test case for ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase.
 */
class EzcDatabaseTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/languages.php' );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::__construct
     */
    public function testCtor()
    {
        $handlerMock = $this->getDatabaseHandler();
        $gateway = new EzcDatabase( $handlerMock );

        $this->assertAttributeSame(
            $handlerMock,
            'dbHandler',
            $gateway
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::insertGroup
     */
    public function testInsertGroup()
    {
        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $group = $this->getGroupFixture();

        $id = $gateway->insertGroup( $group );

        $this->assertQueryResult(
            array(
                array(
                    'id' => '1',
                    'created' => '1032009743',
                    'creator_id' => '14',
                    'modified' => '1033922120',
                    'modifier_id' => '14',
                    'name' => 'Media',
                )
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
                ->from( 'ezcontentclassgroup' )
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
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::updateGroup
     */
    public function testUpdateGroup()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_groups.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $struct = $this->getGroupUpdateStructFixture();

        $res = $gateway->updateGroup( $struct );

        $this->assertQueryResult(
            array(
                array( '3' )
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select( 'COUNT(*)' )
                ->from( 'ezcontentclassgroup' )
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
            ->from( 'ezcontentclassgroup' )
            ->where(
                $q->expr->eq( 'id', 2 )
            );
        $this->assertQueryResult(
            array(
                array(
                    'id' => 2,
                    'created' => 1031216941,
                    'creator_id' => 14,
                    'modified' => 1311454096,
                    'modifier_id' => 23,
                    'name' => 'UpdatedGroupName',
                ),
            ),
            $q
        );
    }

    /**
     * Returns a Group update struct fixture.
     *
     * @return \ezp\Persistence\Content\Type\Group\UpdateStruct
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
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::countTypesInGroup
     */
    public function testCountTypesInGroup()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $this->assertEquals(
            3,
            $gateway->countTypesInGroup( 1 )
        );
        $this->assertEquals(
            0,
            $gateway->countTypesInGroup( 23 )
        );
    }

    public function testCountGroupsForType()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $this->assertEquals(
            1,
            $gateway->countGroupsForType( 1, 1 )
        );
        $this->assertEquals(
            0,
            $gateway->countGroupsForType( 23, 0 )
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::deleteGroup
     */
    public function testDeleteGroup()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_groups.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $gateway->deleteGroup( 2 );

        $this->assertQueryResult(
            array(
                array( '1' ),
                array( '3' ),
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select( 'id' )
                ->from( 'ezcontentclassgroup' )
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::loadGroupData
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::createGroupLoadQuery
     * @return void
     */
    public function testLoadGroupData()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_groups.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $data = $gateway->loadGroupData( 2 );

        $this->assertSame(
            array(
                array(
                    'created' => '1031216941',
                    'creator_id' => '14',
                    'id' => '2',
                    'modified' => '1033922113',
                    'modifier_id' => '14',
                    'name' => 'Users',
                )
            ),
            $data
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::loadAllGroupsData
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::createGroupLoadQuery
     * @return void
     */
    public function testLoadAllGroupsData()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_groups.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $data = $gateway->loadAllGroupsData();

        $this->assertEquals(
            3,
            count( $data )
        );

        $this->assertSame(
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

    public function testLoadTypesDataForGroup()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $rows = $gateway->loadTypesDataForGroup( 1, 0 );

        $this->assertEquals(
            6,
            count( $rows )
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::loadTypeData
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::selectColumns
     */
    public function testLoadTypeData()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $rows = $gateway->loadTypeData( 1, 0 );

        $this->assertEquals(
            5,
            count( $rows )
        );
        $this->assertEquals(
            45,
            count( $rows[0] )
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

    public static function getTypeCreationExpectations()
    {
        return array(
            array( 'always_available', 0 ),
            array( 'contentobject_name', '<short_name|name>' ),
            array( 'created', '1024392098' ),
            array( 'creator_id', '14' ),
            array( 'identifier', 'folder' ),
            array( 'initial_language_id', '2' ),
            array( 'is_container', '1' ),
            array( 'language_mask', 7 ),
            array( 'modified', '1082454875' ),
            array( 'modifier_id', '14' ),
            array( 'remote_id', 'a3d405b81be900468eb153d774f4f0d2' ),
            array( 'serialized_description_list', 'a:2:{i:0;s:0:"";s:16:"always-available";b:0;}' ),
            array( 'serialized_name_list', 'a:3:{s:16:"always-available";s:6:"eng-US";s:6:"eng-US";s:6:"Folder";s:6:"eng-GB";s:11:"Folder (GB)";}' ),
            array( 'sort_field', 1 ),
            array( 'sort_order', 1 ),
            array( 'url_alias_name', '' ),
            array( 'version', '0' ),
        );
    }

    /**
     * @dataProvider getTypeCreationExpectations
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::insertType
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::setCommonTypeColumns
     */
    public function testInsertType( $column, $expectation )
    {
        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $type = $this->getTypeFixture();

        $gateway->insertType( $type );

        $this->assertQueryResult(
            array( array($expectation ) ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select( $column )
                ->from( 'ezcontentclass' ),
            'Inserted Type data incorrect in column ' . $column
        );
    }

    public static function getTypeCreationContentClassNameExpectations()
    {
        return array(
            array( 'contentclass_id', array( 1, 1 ) ),
            array( 'contentclass_version', array( 0, 0 ) ),
            array( 'language_id', array( 3, 4 ) ),
            array( 'language_locale', array( 'eng-US', 'eng-GB' ) ),
            array( 'name', array( 'Folder', 'Folder (GB)' ) ),
        );
    }

    /**
     * @dataProvider getTypeCreationContentClassNameExpectations
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::insertType
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::setCommonTypeColumns
     */
    public function testInsertTypeContentClassName( $column, $expectation )
    {
        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $type = $this->getTypeFixture();

        $gateway->insertType( $type );

        $this->assertQueryResult(
            array_map( function( $value ) { return array( $value ); }, $expectation ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select( $column )
                ->from( 'ezcontentclass_name' ),
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

        return $type;
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::insertFieldDefinition
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::setCommonFieldColumns
     */
    public function testInsertFieldDefinition()
    {
        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $field = $this->getFieldDefinitionFixture();

        $gateway->insertFieldDefinition( 23, 1, $field );

        $this->assertQueryResult(
            array(
                array(
                    'contentclass_id' => '23',
                    'serialized_name_list' => 'a:2:{s:16:"always-available";s:6:"eng-US";s:6:"eng-US";s:11:"Description";}',
                    'serialized_description_list' => 'a:2:{s:16:"always-available";s:6:"eng-GB";s:6:"eng-GB";s:16:"Some description";}',
                    'identifier' => 'description',
                    'category' => '',
                    'placement' => '4',
                    'data_type_string' => 'ezxmltext',
                    'can_translate' => '1',
                    'is_required' => '1',
                    'is_information_collector' => '1',
                    'serialized_data_text' => 'a:2:{i:0;s:0:"";s:16:"always-available";b:0;}',
                    'version' => '1',
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
                    'version'
                )
                ->from( 'ezcontentclass_attribute' ),
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
        $field->fieldGroup = '';
        $field->position = 4;
        $field->fieldType = 'ezxmltext';
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
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::deleteFieldDefinition
     */
    public function testDeleteFieldDefinition()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $gateway->deleteFieldDefinition( 1, 0, 119 );

        $this->assertQueryResult(
            array( array( 6 ) ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select( 'COUNT(*)' )
                ->from( 'ezcontentclass_attribute' )
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::updateFieldDefinition
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::setCommonFieldColumns
     */
    public function testUpdateFieldDefinition()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );
        $fieldDefinitionFixture = $this->getFieldDefinitionFixture();
        $fieldDefinitionFixture->id = 160;

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $gateway->updateFieldDefinition( 2, 0, $fieldDefinitionFixture );

        $this->assertQueryResult(
            array(
                // "random" sample
                array(
                    'category' => '',
                    'contentclass_id' => '2',
                    'version' => '0',
                    'data_type_string' => 'ezxmltext',
                    'identifier' => 'description',
                    'is_information_collector' => '1',
                    'placement' => '4',
                    'serialized_description_list' => 'a:2:{s:16:"always-available";s:6:"eng-GB";s:6:"eng-GB";s:16:"Some description";}',
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
                    'serialized_description_list'
                )
                ->from( 'ezcontentclass_attribute' )
                ->where( 'id = 160' ),
            'FieldDefinition not updated correctly'
        );

    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::insertGroupAssignement
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::loadGroupData
     */
    public function testInsertGroupAssignement()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_groups.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $gateway->insertGroupAssignement( 3, 42, 1 );

        $this->assertQueryResult(
            array(
                array(
                    'contentclass_id' => '42',
                    'contentclass_version' => '1',
                    'group_id' => '3',
                    'group_name' => 'Media',
                )
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    'contentclass_id',
                    'contentclass_version',
                    'group_id',
                    'group_name'
                )->from( 'ezcontentclass_classgroup' )
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::deleteGroupAssignement
     */
    public function testDeleteGroupAssignement()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $gateway->deleteGroupAssignement( 1, 1, 0 );

        $this->assertQueryResult(
            array( array( '1' ) ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    'COUNT(*)'
                )->from( 'ezcontentclass_classgroup' )
                ->where( 'contentclass_id = 1' )
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::updateType
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::setCommonTypeColumns
     */
    public function testUpdateType()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $updateStruct = $this->getTypeUpdateFixture();

        $gateway->updateType( 1, 0, $updateStruct );

        $this->assertQueryResult(
            array(
                array(
                    // "random" sample
                    'serialized_name_list' => 'a:2:{s:16:"always-available";s:6:"eng-US";s:6:"eng-US";s:10:"New Folder";}',
                    'created' => '1024392098',
                    'modifier_id' => '42',
                    'remote_id' => 'foobar',
                )
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    'serialized_name_list',
                    'created',
                    'modifier_id',
                    'remote_id'
                )->from( 'ezcontentclass' )
                ->where( 'id = 1 AND version = 0' ),
            'Inserted Type data incorrect'
        );

    }

    /**
     * Returns a ezp\Persistence\Content\Type\UpdateStruct fixture.
     *
     * @return \ezp\Persistence\Content\Type\UpdateStruct
     */
    protected function getTypeUpdateFixture()
    {
        $struct = new UpdateStruct();

        $struct->name = array(
            'always-available' => 'eng-US',
            'eng-US' => 'New Folder',
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

        return $struct;
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::deleteFieldDefinitionsForType
     */
    public function testDeleteFieldDefinitionsForTypeExisting()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $gateway->deleteFieldDefinitionsForType( 1, 0 );

        $countAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countAffectedAttr
            ->select( 'COUNT(*)' )
            ->from( 'ezcontentclass_attribute' )
            ->where(
                $countAffectedAttr->expr->eq(
                    'contentclass_id',
                    1
                )
            );
        // 1 left with version 1
        $this->assertQueryResult(
            array( array( 1 ) ),
            $countAffectedAttr
        );

        $countNotAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countNotAffectedAttr->select( 'COUNT(*)' )
            ->from( 'ezcontentclass_attribute' );

        $this->assertQueryResult(
            array( array( 2 ) ),
            $countNotAffectedAttr
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::deleteFieldDefinitionsForType
     */
    public function testDeleteFieldDefinitionsForTypeNotExisting()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $gateway->deleteFieldDefinitionsForType( 23, 1 );

        $countNotAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countNotAffectedAttr->select( 'COUNT(*)' )
            ->from( 'ezcontentclass_attribute' );

        $this->assertQueryResult(
            array( array( 7 ) ),
            $countNotAffectedAttr
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::deleteGroupAssignementsForType
     */
    public function testDeleteGroupAssignementsForTypeExisting()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $gateway->deleteGroupAssignementsForType( 1, 0 );

        $countAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countAffectedAttr->select( 'COUNT(*)' )
            ->from( 'ezcontentclass_classgroup' );

        $this->assertQueryResult(
            array( array( 2 ) ),
            $countAffectedAttr
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::deleteGroupAssignementsForType
     */
    public function testDeleteGroupAssignementsForTypeNotExisting()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $gateway->deleteType( 23, 1 );

        $countAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countAffectedAttr->select( 'COUNT(*)' )
            ->from( 'ezcontentclass_classgroup' );

        $this->assertQueryResult(
            array( array( 3 ) ),
            $countAffectedAttr
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::deleteType
     */
    public function testDeleteTypeExisting()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $gateway->deleteType( 1, 0 );

        $countAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countAffectedAttr->select( 'COUNT(*)' )
            ->from( 'ezcontentclass' );

        $this->assertQueryResult(
            array( array( 1 ) ),
            $countAffectedAttr
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Gateway\EzcDatabase::deleteType
     */
    public function testDeleteTypeNotExisting()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/existing_types.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $gateway->deleteType( 23, 1 );

        $countAffectedAttr = $this->getDatabaseHandler()
            ->createSelectQuery();
        $countAffectedAttr->select( 'COUNT(*)' )
            ->from( 'ezcontentclass' );

        $this->assertQueryResult(
            array( array( 2 ) ),
            $countAffectedAttr
        );
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
