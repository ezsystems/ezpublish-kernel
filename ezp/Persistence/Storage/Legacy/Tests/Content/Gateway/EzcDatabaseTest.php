<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Type\Gateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Gateway;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,

    ezp\Persistence\Content,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\Version;

/**
 * Test case for ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase.
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase::__construct
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
     * @covers ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase::insertContentObject
     * @todo Fix not available fields
     */
    public function testInsertContentObject()
    {
        $content = $this->getContentFixture();

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $gateway->insertContentObject( $content );

        $this->assertQueryResult(
            array(
                array(
                    'contentclass_id'     => '23',
                    'current_version'     => 1,
                    // @FIXME
                    'initial_language_id' => 0,
                    // @FIXME
                    'language_mask'       => 0,
                    // @FIXME
                    'modified'            => 0,
                    'name'                => 'Content name',
                    'owner_id'            => '13',
                    // @FIXME
                    'published'           => 0,
                    // @FIXME
                    'remote_id'           => null,
                    'section_id'          => '42',
                    // @FIXME
                    'status'              => 0,
                ),
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    array(
                        'contentclass_id',
                        'current_version',
                        'initial_language_id',
                        'language_mask',
                        'modified',
                        'name',
                        'owner_id',
                        'published',
                        'remote_id',
                        'section_id',
                        'status',
                    )
                )->from( 'ezcontentobject' )
        );
    }

    /**
     * Returns a Content fixture
     *
     * @return Content
     */
    protected function getContentFixture()
    {
        $struct = new Content();

        $struct->name            = 'Content name';
        $struct->typeId          = 23;
        $struct->sectionId       = 42;
        $struct->ownerId         = 13;
        $struct->locations       = array();

        return $struct;
    }

    public function testInsertVersion()
    {
        $version = $this->getVersionFixture();

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $gateway->insertVersion( $version );

        $this->assertQueryResult(
            array(
                array(
                    'contentobject_id'    => '2342',
                    'created'             => '1312278322',
                    'creator_id'          => '13',
                    // @FIXME
                    'initial_language_id' => '0',
                    // @FIXME
                    'language_mask'       => '0',
                    'modified'            => '1312278323',
                    'status'              => '0',
                    // @FIXME
                    'user_id'             => '0',
                    'version'             => '1',
                    'workflow_event_pos'  => '0',

                )
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    array(
                        'contentobject_id',
                        'created',
                        'creator_id',
                        'initial_language_id',
                        'language_mask',
                        'modified',
                        'status',
                        'user_id',
                        'version',
                        'workflow_event_pos',
                    )
                )->from( 'ezcontentobject_version' )
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase::updateVersion
     * @return void
     */
    public function testUpdateVersion()
    {
        $gateway = new EzcDatabase( $this->getDatabaseHandler() );

        $time        = time();
        $version     = $this->getVersionFixture();
        $version->id = $gateway->insertVersion( $version );

        $gateway->updateVersion( $version->id, 2, 14 );

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $this->assertQueryResult(
            array( array( 2, 14 ) ),
            $query
                ->select( array( 'version', 'user_id' ) )
                ->from( 'ezcontentobject_version' )
                ->where( $query->expr->lAnd(
                    $query->expr->eq( 'id', $query->bindValue( $version->id ) ),
                    $query->expr->gte( 'modified', $time )
                ) )
        );
    }

    /**
     * Returns a Version fixture
     *
     * @return Version
     */
    protected function getVersionFixture()
    {
        $version = new Version();

        $version->id        = null;
        $version->versionNo = 1;
        $version->creatorId = 13;
        $version->state     = 0;
        $version->contentId = 2342;
        $version->fields    = array();
        $version->created   = 1312278322;
        $version->modified  = 1312278323;

        return $version;
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase::insertNewField
     * @return void
     */
    public function testInsertNewField()
    {
        $content = $this->getContentFixture();
        $content->id = 2342;

        $field = $this->getFieldFixture();
        $value = $this->getStorageValueFixture();

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $gateway->insertNewField( $content, $field, $value );

        $this->assertQueryResult(
            array(
                array(
                    // @FIXME
                    'attribute_original_id'    => '0',
                    'contentclassattribute_id' => '231',
                    'contentobject_id'         => '2342',
                    'data_float'               => '24.42',
                    'data_int'                 => '42',
                    'data_text'                => 'Test text',
                    'data_type_string'         => 'ezstring',
                    // @FIXME Is language_code correct?
                    'language_code'            => '31',
                    // @FIXME
                    'language_id'              => 0,
                    'sort_key_int'             => '23',
                    'sort_key_string'          => 'Test',
                    'version'                  => '1',
                )
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    array(
                        'attribute_original_id',
                        'contentclassattribute_id',
                        'contentobject_id',
                        'data_float',
                        'data_int',
                        'data_text',
                        'data_type_string',
                        'language_code',
                        'language_id',
                        'sort_key_int',
                        'sort_key_string',
                        'version',
                    )
                )->from( 'ezcontentobject_attribute' )
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase::updateField
     * @return void
     */
    public function testUpdateField()
    {
        $content = $this->getContentFixture();
        $content->id = 2342;

        $field = $this->getFieldFixture();
        $value = $this->getStorageValueFixture();

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $field->id = $gateway->insertNewField( $content, $field, $value );

        $newValue = new StorageFieldValue( array(
            'dataFloat'     => 124.42,
            'dataInt'       => 142,
            'dataText'      => 'New text',
            'sortKeyInt'    => 123,
            'sortKeyString' => 'new_text',
        ) );

        $gateway->updateField( $field, $newValue );

        $this->assertQueryResult(
            array(
                array(
                    'data_float'               => '124.42',
                    'data_int'                 => '142',
                    'data_text'                => 'New text',
                    'sort_key_int'             => '123',
                    'sort_key_string'          => 'new_text',
                )
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    array(
                        'data_float',
                        'data_int',
                        'data_text',
                        'sort_key_int',
                        'sort_key_string',
                    )
                )->from( 'ezcontentobject_attribute' )
        );
    }

    public function testListVersions()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = new EzcDatabase( $this->getDatabaseHandler() );
        $res = $gateway->listVersions( 226 );

        $this->assertEquals(
            2,
            count( $res )
        );

        foreach ( $res as $row )
        {
            $this->assertEquals(
                9,
                count( $row )
            );
        }

        $this->assertEquals(
            675,
            $res[0]['ezcontentobject_version_id']
        );
        $this->assertEquals(
            676,
            $res[1]['ezcontentobject_version_id']
        );

        /*
        $this->storeFixture(
            __DIR__ . '/../_fixtures/restricted_version_rows.php',
            $res
        );
        */
    }

    protected function storeFixture( $file, $fixture )
    {
        file_put_contents(
            $file,
            "<?php\n\nreturn " . var_export( $fixture, true ) . ";\n"
        );
    }

    /**
     * Returns a Field fixture
     *
     * @return Field
     */
    protected function getFieldFixture()
    {
        $field = new Field();

        $field->fieldDefinitionId = 231;
        $field->type              = 'ezstring';
        $field->language          = 31;
        $field->versionNo         = 1;

        return $field;
    }

    /**
     * Returns a StorageFieldValue fixture
     *
     * @return StorageFieldValue
     */
    protected function getStorageValueFixture()
    {
        $value = new StorageFieldValue();

        $value->dataFloat     = 24.42;
        $value->dataInt       = 42;
        $value->dataText      = 'Test text';
        $value->sortKeyInt    = 23;
        $value->sortKeyString = 'Test';

        return $value;
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
