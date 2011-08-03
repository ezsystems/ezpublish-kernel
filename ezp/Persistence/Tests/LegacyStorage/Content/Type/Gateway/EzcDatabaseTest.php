<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\Type\Gateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegacyStorage\Content\Type\Gateway;
use ezp\Persistence\Tests\LegacyStorage\TestCase,
    ezp\Persistence\Tests\LegacyStorage\Content\Type\Gateway,
    ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase,

    ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\UpdateStruct,
    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;

/**
 * Test case for ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase.
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::__construct
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::insertGroup
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::updateGroup
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
     * @return ezp\Persistence\Content\Type\Group\UpdateStruct
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::loadTypeData
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::selectColumns
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::createTableColumnAlias
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::qualifiedIdentifier
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

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::insertType
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::setCommonTypeColumns
     */
    public function testInsertType()
    {
        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $type = $this->getTypeFixture();

        $gateway->insertType( $type );

        $this->assertQueryResult(
            array( array( 1 ) ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select( 'COUNT(*)' )
                ->from( 'ezcontentclass' ),
            'Type not inserted'
        );
        $this->assertQueryResult(
            array(
                array(
                    'version' => '0',
                    'serialized_name_list' => 'a:2:{s:16:"always-available";s:6:"eng-US";s:6:"eng-US";s:6:"Folder";}',
                    'serialized_description_list' => 'a:2:{i:0;s:0:"";s:16:"always-available";b:0;}',
                    'identifier' => 'folder',
                    'created' => '1024392098',
                    'modified' => '1082454875',
                    'creator_id' => '14',
                    'modifier_id' => '14',
                    'remote_id' => 'a3d405b81be900468eb153d774f4f0d2',
                    'url_alias_name' => '',
                    'is_container' => '1',
                    'initial_language_id' => '2',
                )
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    'version',
                    'serialized_name_list',
                    'serialized_description_list',
                    'identifier',
                    'created',
                    'modified',
                    'creator_id',
                    'modifier_id',
                    'remote_id',
                    'url_alias_name',
                    'is_container',
                    'initial_language_id'
                )->from( 'ezcontentclass' ),
            'Inserted Type data incorrect'
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

        $type->version = 0;
        $type->name = array(
            'always-available' => 'eng-US',
            'eng-US' => 'Folder',
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::insertFieldDefinition
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::setCommonFieldColumns
     */
    public function testInsertFieldDefinition()
    {
        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $field = $this->getFieldDefinitionFixture();

        $gateway->insertFieldDefinition( 23, 1, $field );

        $this->assertQueryResult(
            array(
                // "random" sample
                array(
                    'category' => '',
                    'contentclass_id' => 23,
                    'version' => 1,
                    'data_type_string' => 'ezxmltext',
                    'identifier' => 'description',
                    'is_required' => '0',
                    'placement' => '4',
                    'serialized_name_list' => 'a:2:{s:16:"always-available";s:6:"eng-US";s:6:"eng-US";s:11:"Description";}',
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
                    'is_required',
                    'placement',
                    'serialized_name_list'
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
        $field->isRequired = false;
        $field->isInfoCollector = false;
        // $field->fieldTypeConstraints ???
        $field->defaultValue = array(
            0 => '',
            'always-available' => false,
        );

        return $field;
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::deleteFieldDefinition
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::updateFieldDefinition
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::setCommonFieldColumns
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
                    'is_information_collector' => '0',
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::insertGroupAssignement
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::loadGroupData
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::deleteGroupAssignement
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::updateType
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::setCommonTypeColumns
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
     * @return ezp\Persistence\Content\Type\UpdateStruct
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::deleteFieldDefinitionsForType
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::deleteFieldDefinitionsForType
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::deleteGroupAssignementsForType
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::deleteGroupAssignementsForType
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::deleteType
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\Gateway\EzcDatabase::deleteType
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
