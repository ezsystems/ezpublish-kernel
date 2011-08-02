<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\Type\ContentTypeGateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegacyStorage\Content\ContentGateway;
use ezp\Persistence\Tests\LegacyStorage\TestCase,
    ezp\Persistence\LegacyStorage\Content\ContentGateway\EzcDatabase,
    ezp\Persistence\LegacyStorage\Content\StorageFieldValue,

    ezp\Persistence\Content,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\Version;

/**
 * Test case for ContentGateway.
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\ContentGateway\EzcDatabase::__construct
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
     * @covers ezp\Persistence\LegacyStorage\Content\ContentGateway\EzcDatabase::insertContentObject
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
        $struct->versionInfos    = array();
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
