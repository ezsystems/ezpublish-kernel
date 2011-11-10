<?php
/**
 * File contains: ezp\Persistence\Tests\ContentHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;
use ezp\Persistence\Content,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\Relation as RelationValue,
    ezp\Persistence\Content\Query\Criterion\ContentId,
    ezp\Base\Exception\NotFound,
    ezp\Content as ContentDomainObject,
    ezp\Content\Version,
    ezp\Content\Relation,
    ezp\Content\FieldType\TextLine\Value as TextLineValue;

/**
 * Test case for ContentHandler using in memory storage.
 *
 */
class ContentHandlerTest extends HandlerTest
{
    /**
     * @var \ezp\Content
     */
    protected $content;

    /**
     *
     * @var int
     */
    protected $contentId;

    /**
     * @var \ezp\Content[]
     */
    protected $contentToDelete = array();

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $struct = new CreateStruct();
        $struct->name = "test";
        $struct->ownerId = 14;
        $struct->sectionId = 1;
        $struct->typeId = 2;
        $struct->fields[] = new Field(
            array(
                'type' => 'ezstring',
                // FieldValue object compatible with ezstring
                'value' => new FieldValue(
                    array(
                        'data' => new TextLineValue( "Welcome" )
                    )
                ),
                'language' => 'eng-GB',
            )
        );

        $this->content = $this->persistenceHandler->contentHandler()->create( $struct );
        $this->contentToDelete[] = $this->content;
        $this->contentId = $this->content->id;
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
        $contentHandler = $this->persistenceHandler->contentHandler();

        try
        {
            // Removing default objects as well as those created by tests
            foreach ( $this->contentToDelete as $content )
            {
                $contentHandler->delete( $content->id );
            }
        }
        catch ( NotFound $e )
        {
        }
        unset( $this->contentId );
        //$contentHandler->delete( 2 );
        parent::tearDown();
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::create
     * @group contentHandler
     */
    public function testCreate()
    {
        $struct = new CreateStruct();
        $struct->name = "test";
        $struct->ownerId = 14;
        $struct->sectionId = 1;
        $struct->typeId = 2;
        $struct->fields[] = new Field(
            array(
                'type' => 'ezstring',
                // FieldValue object compatible with ezstring
                "value" => new FieldValue(
                    array(
                        "data" => new TextLineValue( "Welcome" )
                    )
                ),
                'language' => 'eng-GB',
            )
        );

        $content = $this->persistenceHandler->contentHandler()->create( $struct );
        $this->contentToDelete[] = $content;
        $this->assertTrue( $content instanceof Content );
        $this->assertEquals( $this->contentId + 1, $content->id );
        $this->assertEquals( 14, $content->ownerId );
        $this->assertEquals( ContentDomainObject::STATUS_DRAFT, $content->status );

        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Version', $content->version );
        $this->assertEquals( 14, $content->version->creatorId );
        $this->assertEquals( 'test', $content->version->name );
        $this->assertEquals( Version::STATUS_DRAFT, $content->version->status );
        $this->assertEquals( $content->id, $content->version->contentId );
        $this->assertEquals( 1, count( $content->version->fields ) );

        $field = $content->version->fields[0];
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Field', $field );
        $this->assertEquals( 'ezstring', $field->type );
        $this->assertEquals( 'eng-GB', $field->language );
        $this->assertEquals( 'Welcome', $field->value->data->text );
        $this->assertEquals( $content->version->versionNo, $field->versionNo );
    }

    /**
     * Test delete function
     *
     * @covers \ezp\Persistence\Storage\InMemory\ContentHandler::delete
     * @group contentHandler
     */
    public function testDelete()
    {
        $contentHandler = $this->persistenceHandler->contentHandler();
        $contentHandler->delete( $this->content->id );

        try
        {
            $this->persistenceHandler->searchHandler()->findSingle( new ContentId( $this->content->id ) );
            $this->fail( "Content not removed correctly" );
        }
        catch ( NotFound $e )
        {
        }

        try
        {
            $contentHandler->listVersions( $this->content->id );
            $this->fail( "No version should have been returned but a NotFound exception!" );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test copy function
     *
     * @covers \ezp\Persistence\Storage\InMemory\ContentHandler::copy
     * @group contentHandler
     */
    public function testCopyVersion1()
    {
        $time = time();
        $contentHandler = $this->persistenceHandler->contentHandler();
        $copy = $contentHandler->copy( 1, 1 );
        $this->assertEquals( 1, $copy->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->typeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->ownerId, "Owner ID does not match" );
        $this->assertEquals( 1, $copy->currentVersionNo, "Current version no does not match" );
        $this->assertEmpty( $copy->locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $copy->id );
        $this->assertEquals( 1, count( $versions ) );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $versions[0]->name );
        $this->assertEquals( 1, $versions[0]->versionNo, "Version number does not match" );
        $this->assertEquals( 14, $versions[0]->creatorId, "Creator ID does not match" );
        $this->assertEquals( $copy->id, $versions[0]->contentId );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->modified );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->created );
    }

    /**
     * Test copy function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::copy
     * @group contentHandler
     */
    public function testCopyVersion2()
    {
        $time = time();
        $versionNoToCopy = 2;
        $contentHandler = $this->persistenceHandler->contentHandler();
        $copy = $contentHandler->copy( 1, $versionNoToCopy );
        $this->assertEquals( 1, $copy->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->typeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->ownerId, "Owner ID does not match" );
        $this->assertEquals( $versionNoToCopy, $copy->currentVersionNo, "Current version no does not match" );
        $this->assertEmpty( $copy->locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $copy->id );
        $this->assertEquals( 1, count( $versions ) );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $versions[0]->name );
        $this->assertEquals( 2, $versions[0]->versionNo, "Version number does not match" );
        $this->assertEquals( 14, $versions[0]->creatorId, "Creator ID does not match" );
        $this->assertEquals( $copy->id, $versions[0]->contentId );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->modified );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->created );
    }

    /**
     * Test copy function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::copy
     * @group contentHandler
     */
    public function testCopyAllVersions()
    {
        $time = time();
        $contentHandler = $this->persistenceHandler->contentHandler();
        $copy = $contentHandler->copy( 1, false );
        $this->assertEquals( 1, $copy->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->typeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->ownerId, "Owner ID does not match" );
        $this->assertEquals( 1, $copy->currentVersionNo, "Current version no does not match" );
        $this->assertEmpty( $copy->locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $copy->id );
        $this->assertEquals( 2, count( $versions ) );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $versions[0]->name );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $versions[1]->name );
        $this->assertEquals( 1, $versions[0]->versionNo );
        $this->assertEquals( 2, $versions[1]->versionNo );
        $this->assertEquals( 14, $versions[0]->creatorId );
        $this->assertEquals( 14, $versions[1]->creatorId );
        $this->assertEquals( $copy->id, $versions[0]->contentId );
        $this->assertEquals( $copy->id, $versions[1]->contentId );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->modified );
        $this->assertGreaterThanOrEqual( $time, $versions[1]->modified );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->created );
        $this->assertGreaterThanOrEqual( $time, $versions[1]->created );
    }

    /**
     * Test update function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::update
     * @group contentHandler
     */
    public function testUpdate()
    {
        $struct = new UpdateStruct;
        $struct->id = $this->contentId;
        $struct->versionNo = 1;
        $struct->name = array( "eng-GB" => "New name", "fre-FR" => "Nouveau nom" );
        $struct->creatorId = 10;
        $struct->ownerId = 10;
        $struct->fields[] = new Field(
            array(
                "type" => "ezstring",
                "value" => new FieldValue(
                    array(
                        "data" => new TextLineValue( "Welcome2" )
                    )
                ),
                "language" => "eng-GB",
            )
        );

        $content = $this->persistenceHandler->contentHandler()->update( $struct );
        $this->assertTrue( $content instanceof Content );
        $this->assertEquals( $this->contentId, $content->id );
        $this->assertEquals( ContentDomainObject::STATUS_DRAFT, $content->status );
        $this->assertEquals( 10, $content->ownerId );
        $this->assertEquals( array( "eng-GB" => "New name", "fre-FR" => "Nouveau nom" ), $content->version->name );

        // @todo Test fields!
    }

    /**
     * Tests creatDraftFromVersion()
     *
     * @group contentHandler
     * @covers \ezp\Persistence\Content\Handler::createDraftFromVersion
     */
    public function testCreateDraftFromVersion()
    {
        $time = time();
        $contentHandler = $this->persistenceHandler->contentHandler();
        $content = $contentHandler->copy( 1, 1 );
        $this->contentToDelete[] = $content;

        $draft = $contentHandler->createDraftFromVersion( $content->id, 1 );
        self::assertSame( $content->currentVersionNo + 1, $draft->versionNo );
        self::assertGreaterThanOrEqual( $time, $draft->created );
        self::assertGreaterThanOrEqual( $time, $draft->modified );
        self::assertSame( Version::STATUS_DRAFT, $draft->status, 'Created version must be a draft' );
        self::assertSame( $content->id, $draft->contentId );

        // Indexing fields by defition id to be able to compare them
        $aOriginalIndexedFields = array();
        $aIndexedFields = array();
        foreach ( $content->version->fields as $field )
        {
            $aOriginalIndexedFields[$field->fieldDefinitionId] = $field;
        }

        foreach ( $draft->fields as $field )
        {
            $aIndexedFields[$field->fieldDefinitionId] = $field;
        }

        // Now comparing original version vs new draft
        foreach ( $aOriginalIndexedFields as $definitionId => $field )
        {
            self::assertTrue( isset( $aIndexedFields[$definitionId] ), 'Created version must have the same fields as original version' );
            self::assertSame( $field->type, $aIndexedFields[$definitionId]->type, 'Fields must have the same type' );
            self::assertEquals( $field->value, $aIndexedFields[$definitionId]->value, 'Fields must have the same value' );
            self::assertEquals( $field->language, $aIndexedFields[$definitionId]->language, 'Fields language must be equal' );
            self::assertSame( $field->versionNo + 1, $aIndexedFields[$definitionId]->versionNo, 'Field version number must be incremented' );
        }
    }

    public  function testSetStatus()
    {
        $content = $this->content;

        self::assertEquals( Version::STATUS_DRAFT, $content->version->status );
        $this->persistenceHandler->contentHandler()->setStatus( $content->id, Version::STATUS_PUBLISHED, $content->version->versionNo );
        $content = $this->persistenceHandler->contentHandler()->load( $content->id, $content->version->versionNo );

        self::assertEquals( Version::STATUS_PUBLISHED, $content->version->status );
    }
}
