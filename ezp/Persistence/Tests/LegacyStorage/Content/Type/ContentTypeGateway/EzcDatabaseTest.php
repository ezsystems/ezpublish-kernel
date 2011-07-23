<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegcyStorage\Content\Type\ContentTypeGateway;
use ezp\Persistence\Tests\LegacyStorage\TestCase,
    ezp\Persistence\Tests\LegcyStorage\Content\Type\ContentTypeGateway,
    ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase,

    ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\Group;

/**
 * Test case for ContentTypeGateway.
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase::__construct
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

    public function testInsertGroup()
    {
        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $group = $this->getGroupFixture();

        $id = $gateway->insertGroup( $group );

        $this->assertQueryResult(
            array(
                array(
                    'id'          => '1',
                    'created'     => '1032009743',
                    'creator_id'  => '14',
                    'modified'    => '1033922120',
                    'modifier_id' => '14',
                    'name'        => 'Media',
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
        $group->created    = 1032009743;
        $group->modified   = 1033922120;
        $group->creatorId  = 14;
        $group->modifierId = 14;

        return $group;
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase::loadTypeData
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase::selectColumns
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase::createTableColumnAlias
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase::qualifiedIdentifier
     */
    public function testLoadTypeData()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/_fixtures/load_type.php'
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase::insertType
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
                    // "random" sample
                    'serialized_name_list' => 'a:2:{s:16:"always-available";s:6:"eng-US";s:6:"eng-US";s:6:"Folder";}',
                    'created' => '1024392098',
                    'modifier_id' => '14',
                    'remote_id' => 'a3d405b81be900468eb153d774f4f0d2',
                )
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    'serialized_name_list',
                    'created',
                    'modifier_id',
                    'remote_id'
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabase::insertFieldDefinition
     */
    public function testInsertFieldDefinition()
    {
        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $field = $this->getFieldDefinitionFixture();

        $gateway->insertFieldDefinition( 23, $field );

        $this->assertQueryResult(
            array(
                // "random" sample
                array(
                    'category' => '',
                    'contentclass_id' => 23,
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
            0 => '',
            'always-available' => false,
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
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
