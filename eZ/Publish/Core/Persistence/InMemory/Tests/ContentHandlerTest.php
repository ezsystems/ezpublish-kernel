<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\ContentHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;
use eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Relation as RelationValue,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId,
    eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound,
    ezp\Content as ContentDomainObject,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
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
                        'data' => "Welcome"
                    )
                ),
                'languageCode' => 'eng-GB',
            )
        );

        $this->content = $this->persistenceHandler->contentHandler()->create( $struct );
        $this->contentToDelete[] = $this->content;
        $this->contentId = $this->content->contentInfo->contentId;
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
                $contentHandler->delete( $content->contentInfo->contentId );
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
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::create
     * @group contentHandler
     */
    public function testCreate()
    {
        $struct = new CreateStruct();
        $struct->name = array( 'eng-GB' => "test" );
        $struct->ownerId = 14;
        $struct->sectionId = 1;
        $struct->typeId = 2;
        $struct->fields[] = new Field(
            array(
                'type' => 'ezstring',
                // FieldValue object compatible with ezstring
                "value" => new FieldValue(
                    array(
                        "data" => "Welcome"
                    )
                ),
                'languageCode' => 'eng-GB',
            )
        );

        $content = $this->persistenceHandler->contentHandler()->create( $struct );
        $this->contentToDelete[] = $content;
        $this->assertTrue( $content instanceof Content );
        $this->assertEquals( $this->contentId + 1, $content->contentInfo->contentId );
        $this->assertEquals( 14, $content->contentInfo->ownerId );
        $this->assertEquals( false , $content->contentInfo->isPublished );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo', $content->versionInfo );
        $this->assertEquals( 14, $content->versionInfo->creatorId );
        $this->assertEquals( array( 'eng-GB' => 'test' ), $content->versionInfo->names );
        $this->assertEquals( VersionInfo::STATUS_DRAFT, $content->versionInfo->status );
        $this->assertEquals( $content->contentInfo->contentId, $content->versionInfo->contentId );
        $this->assertEquals( 1, count( $content->fields ) );

        $field = $content->fields[0];
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field', $field );
        $this->assertEquals( 'ezstring', $field->type );
        $this->assertEquals( 'eng-GB', $field->languageCode );
        $this->assertEquals( 'Welcome', $field->value->data->text );
        $this->assertEquals( $content->versionInfo->versionNo, $field->versionNo );
    }

    /**
     * Test delete function
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\ContentHandler::delete
     * @group contentHandler
     */
    public function testDelete()
    {
        $contentHandler = $this->persistenceHandler->contentHandler();
        $contentHandler->delete( $this->content->contentInfo->contentId );

        try
        {
            $this->persistenceHandler->searchHandler()->findSingle( new ContentId( $this->content->contentInfo->contentId ) );
            $this->fail( "Content not removed correctly" );
        }
        catch ( NotFound $e )
        {
        }

        try
        {
            $contentHandler->listVersions( $this->content->contentInfo->contentId );
            $this->fail( "No version should have been returned but a NotFound exception!" );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test copy function
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\ContentHandler::copy
     * @group contentHandler
     */
    public function testCopyVersion1()
    {
        $time = time();
        $contentHandler = $this->persistenceHandler->contentHandler();
        $copy = $contentHandler->copy( 1, 1 );
        $this->assertEquals( 1, $copy->contentInfo->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->contentInfo->contentTypeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->contentInfo->ownerId, "Owner ID does not match" );
        $this->assertEquals( 1, $copy->contentInfo->currentVersionNo, "Current version no does not match" );
        $this->assertEmpty( $copy->locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $copy->contentInfo->contentId );
        $this->assertEquals( 1, count( $versions ) );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $versions[0]->name );
        $this->assertEquals( 1, $versions[0]->versionNo, "Version number does not match" );
        $this->assertEquals( 14, $versions[0]->creatorId, "Creator ID does not match" );
        $this->assertEquals( $copy->contentInfo->contentId, $versions[0]->contentId );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->modified );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->created );
    }

    /**
     * Test copy function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::copy
     * @group contentHandler
     */
    public function testCopyVersion2()
    {
        $time = time();
        $versionNoToCopy = 2;
        $contentHandler = $this->persistenceHandler->contentHandler();
        $copy = $contentHandler->copy( 1, $versionNoToCopy );
        $this->assertEquals( 1, $copy->contentInfo->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->contentInfo->contentTypeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->contentInfo->ownerId, "Owner ID does not match" );
        $this->assertEquals( $versionNoToCopy, $copy->contentInfo->currentVersionNo, "Current version no does not match" );
        $this->assertEmpty( $copy->locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $copy->contentInfo->contentId );
        $this->assertEquals( 1, count( $versions ) );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $versions[0]->name );
        $this->assertEquals( 2, $versions[0]->versionNo, "Version number does not match" );
        $this->assertEquals( 14, $versions[0]->creatorId, "Creator ID does not match" );
        $this->assertEquals( $copy->contentInfo->contentId, $versions[0]->contentId );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->modified );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->created );
    }

    /**
     * Test copy function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::copy
     * @group contentHandler
     */
    public function testCopyAllVersions()
    {
        $time = time();
        $contentHandler = $this->persistenceHandler->contentHandler();
        $copy = $contentHandler->copy( 1, false );
        $this->assertEquals( 1, $copy->contentInfo->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->contentInfo->contentTypeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->contentInfo->ownerId, "Owner ID does not match" );
        $this->assertEquals( 1, $copy->contentInfo->currentVersionNo, "Current version no does not match" );
        $this->assertEmpty( $copy->locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $copy->contentInfo->contentId );
        $this->assertEquals( 2, count( $versions ) );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $versions[0]->name );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $versions[1]->name );
        $this->assertEquals( 1, $versions[0]->versionNo );
        $this->assertEquals( 2, $versions[1]->versionNo );
        $this->assertEquals( 14, $versions[0]->creatorId );
        $this->assertEquals( 14, $versions[1]->creatorId );
        $this->assertEquals( $copy->contentInfo->contentId, $versions[0]->contentId );
        $this->assertEquals( $copy->contentInfo->contentId, $versions[1]->contentId );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->modified );
        $this->assertGreaterThanOrEqual( $time, $versions[1]->modified );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->created );
        $this->assertGreaterThanOrEqual( $time, $versions[1]->created );
    }

    /**
     * Test update function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::update
     * @group contentHandler
     */
    public function testUpdate()
    {
        self::markTestSkipped();
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
                        "data" => "Welcome2"
                    )
                ),
                "languageCode" => "eng-GB",
            )
        );

        $content = $this->persistenceHandler->contentHandler()->update( $struct );
        $this->assertTrue( $content instanceof Content );
        $this->assertEquals( $this->contentId, $content->contentInfo->contentId );
        $this->assertEquals( ContentDomainObject::STATUS_DRAFT, $content->status );
        $this->assertEquals( 10, $content->ownerId );
        $this->assertEquals( array( "eng-GB" => "New name", "fre-FR" => "Nouveau nom" ), $content->versionInfo->name );

        // @todo Test fields!
    }

    /**
     * Tests creatDraftFromVersion()
     *
     * @group contentHandler
     * @covers \eZ\Publish\SPI\Persistence\Content\Handler::createDraftFromVersion
     */
    public function testCreateDraftFromVersion()
    {
        $time = time();
        $contentHandler = $this->persistenceHandler->contentHandler();
        $content = $contentHandler->copy( 1, 1 );
        $this->contentToDelete[] = $content;

        $draft = $contentHandler->createDraftFromVersion( $content->contentInfo->contentId, 1 );
        self::assertSame( $content->contentInfo->currentVersionNo + 1, $draft->versionInfo->versionNo );
        self::assertGreaterThanOrEqual( $time, $draft->versionInfo->creationDate );
        self::assertGreaterThanOrEqual( $time, $draft->versionInfo->modificationDate );
        self::assertSame( VersionInfo::STATUS_DRAFT, $draft->versionInfo->status, 'Created version must be a draft' );
        self::assertSame( $content->contentInfo->contentId, $draft->versionInfo->contentId );

        // Indexing fields by defition id to be able to compare them
        $aOriginalIndexedFields = array();
        $aIndexedFields = array();
        foreach ( $content->fields as $field )
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
            self::assertEquals( $field->languageCode, $aIndexedFields[$definitionId]->languageCode, 'Fields language code must be equal' );
            self::assertSame( $field->versionNo + 1, $aIndexedFields[$definitionId]->versionNo, 'Field version number must be incremented' );
        }
    }

    public function testSetStatus()
    {
        $content = $this->content;

        self::assertEquals( VersionInfo::STATUS_DRAFT, $content->versionInfo->status );
        $this->persistenceHandler->contentHandler()->setStatus( $content->contentInfo->contentId, VersionInfo::STATUS_PUBLISHED, $content->versionInfo->versionNo );
        $content = $this->persistenceHandler->contentHandler()->load( $content->contentInfo->contentId, $content->versionInfo->versionNo );

        self::assertEquals( VersionInfo::STATUS_PUBLISHED, $content->versionInfo->status );
    }
}
