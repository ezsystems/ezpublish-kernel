<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\ContentHandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct as TypeCreateStruct;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * Test case for ContentHandler using in memory storage.
 */
class ContentHandlerTest extends HandlerTest
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content
     */
    protected $content;

    /**
     * @var int
     */
    protected $contentId;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content[]
     */
    protected $contentToDelete = array();

    /**
     * ContentTypes which should be removed in tearDown
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type[]
     */
    protected $contentTypeToDelete = array();

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $type = $this->persistenceHandler->contentTypeHandler()->create( $this->getTypeCreateStruct() );
        $this->contentTypeToDelete[] = $type;

        $struct = new CreateStruct();
        $struct->name = array( "eng-GB" => "Welcome" );
        $struct->ownerId = 14;
        $struct->sectionId = 1;
        $struct->typeId = $type->id;
        $struct->initialLanguageId = 2;
        $struct->fields[] = new Field(
            array(
                'type' => 'ezstring',
                'fieldDefinitionId' => $type->fieldDefinitions[0]->id,
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
        $this->contentId = $this->content->versionInfo->contentInfo->id;
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
                $contentHandler->deleteContent( $content->versionInfo->contentInfo->id );
            }
        }
        catch ( NotFound $e )
        {
        }
        unset( $this->contentId );

        $contentTypeHandler = $this->persistenceHandler->contentTypeHandler();
        foreach ( $this->contentTypeToDelete as $type )
        {
            try
            {
                $contentTypeHandler->delete( $type->id, $type->status );
            }
            catch ( NotFound $e )
            {
            }
        }

        parent::tearDown();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct
     */
    protected function getTypeCreateStruct()
    {
        $struct = new TypeCreateStruct();
        $struct->created = $struct->modified = time();
        $struct->creatorId = $struct->modifierId = 14;
        $struct->name = array( 'eng-GB' => 'Article' );
        $struct->description = array( 'eng-GB' => 'Article content type' );
        $struct->identifier = 'article';
        $struct->isContainer = true;
        $struct->status = Type::STATUS_DEFINED;
        $struct->initialLanguageId = 2;
        $struct->nameSchema = "<short_title|title>";
        $struct->fieldDefinitions = array();
        $struct->groupIds = array( 1 );
        $struct->fieldDefinitions[] = $field = $this->getTypeFieldDefinition();
        return $struct;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    protected function getTypeFieldDefinition()
    {
        $field = new FieldDefinition();
        $field->identifier = 'title';
        $field->fieldType = 'ezstring';
        $field->position = 0;
        $field->isTranslatable = $field->isRequired = true;
        $field->isInfoCollector = false;
        $field->defaultValue = new FieldValue(
            array(
                "data" => "New Article"
            )
        );
        $field->name = array( 'eng-GB' => "Title" );
        $field->description = array( 'eng-GB' => "Title, used for headers, and url if short_title is empty" );
        return $field;
    }

    /**
     * Test create function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::create
     * @group contentHandler
     */
    public function testCreate()
    {
        $type = $this->persistenceHandler->contentTypeHandler()->create( $this->getTypeCreateStruct() );
        $this->contentTypeToDelete[] = $type;

        $struct = new CreateStruct();
        $struct->name = array( 'eng-GB' => "test" );
        $struct->ownerId = 14;
        $struct->sectionId = 1;
        $struct->typeId = $type->id;
        $struct->initialLanguageId = 8;
        $struct->modified = time();
        $fieldDefinition = reset( $type->fieldDefinitions );
        $struct->fields[] = new Field(
            array(
                "fieldDefinitionId" => $fieldDefinition->id,
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
        $this->assertEquals( $this->contentId + 1, $content->versionInfo->contentInfo->id );
        $this->assertEquals( 14, $content->versionInfo->contentInfo->ownerId );
        $this->assertEquals( false, $content->versionInfo->contentInfo->isPublished );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo', $content->versionInfo );
        $this->assertEquals( 14, $content->versionInfo->creatorId );
        $this->assertEquals( array( 'eng-GB' => 'test' ), $content->versionInfo->names );
        $this->assertEquals( VersionInfo::STATUS_DRAFT, $content->versionInfo->status );
        $this->assertGreaterThanOrEqual( $struct->modified, $content->versionInfo->creationDate );
        $this->assertGreaterThanOrEqual( $struct->modified, $content->versionInfo->modificationDate );
        $this->assertEquals( "eng-GB", $content->versionInfo->initialLanguageCode );
        $this->assertEquals( array( $struct->initialLanguageId ), $content->versionInfo->languageIds );
        $this->assertEquals( $content->versionInfo->contentInfo->id, $content->versionInfo->contentInfo->id );
        $this->assertEquals( 1, count( $content->fields ) );

        $field = $content->fields[0];
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field', $field );
        $this->assertEquals( 'ezstring', $field->type );
        $this->assertEquals( 'eng-GB', $field->languageCode );
        $this->assertEquals( 'Welcome', $field->value->data );
        $this->assertEquals( $content->versionInfo->versionNo, $field->versionNo );
    }

    /**
     * Test publish function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::publish
     * @group contentHandler
     */
    public function testPublish()
    {
        $contentHandler = $this->persistenceHandler->contentHandler();
        $time = time();
        $metadataUpdateStruct = new MetadataUpdateStruct( array( "modificationDate" => $time ) );

        $publishedContent = $contentHandler->publish( 1, 2, $metadataUpdateStruct );

        $this->assertEquals( 2, $publishedContent->versionInfo->contentInfo->currentVersionNo );
        $this->assertTrue( $publishedContent->versionInfo->contentInfo->isPublished );
        $this->assertEquals( $time, $publishedContent->versionInfo->contentInfo->modificationDate );

        $this->assertEquals( VersionInfo::STATUS_PUBLISHED, $publishedContent->versionInfo->status );
        $this->assertEquals( $time, $publishedContent->versionInfo->modificationDate );
    }

    /**
     * Test for the updateMetadata() function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::updateMetadata
     * @group contentHandler
     */
    public function testUpdateMetadata()
    {
        $contentHandler = $this->persistenceHandler->contentHandler();
        $updateStruct = new MetadataUpdateStruct(
            array(
                "ownerId" => 10,
                "name" => "the all new name",
                "publicationDate" => time(),
                "modificationDate" => time(),
                "mainLanguageId" => 8,
                "alwaysAvailable" => false,
                "remoteId" => "the-all-new-remoteid"
            )
        );

        $contentInfo = $contentHandler->updateMetadata( 4, $updateStruct );

        $this->assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\ContentInfo", $contentInfo );
        $this->assertEquals( $updateStruct->ownerId, $contentInfo->ownerId );
        $this->assertEquals( $updateStruct->name, $contentInfo->name );
        $this->assertEquals( $updateStruct->publicationDate, $contentInfo->publicationDate );
        $this->assertEquals( $updateStruct->modificationDate, $contentInfo->modificationDate );
        $this->assertEquals( "eng-GB", $contentInfo->mainLanguageCode );
        $this->assertFalse( $contentInfo->alwaysAvailable );
        $this->assertEquals( $updateStruct->remoteId, $contentInfo->remoteId );
    }

    /**
     * Test delete function
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\ContentHandler::deleteContent
     * @group contentHandler
     */
    public function testDelete()
    {
        $contentHandler = $this->persistenceHandler->contentHandler();
        $contentHandler->deleteContent( $this->content->versionInfo->contentInfo->id );

        try
        {
            $this->persistenceHandler->searchHandler()->findSingle( new ContentId( $this->content->versionInfo->contentInfo->id ) );
            $this->fail( "Content not removed correctly" );
        }
        catch ( NotFound $e )
        {
        }

        try
        {
            $contentHandler->listVersions( $this->content->versionInfo->contentInfo->id );
            $this->fail( "No version should have been returned but a NotFound exception!" );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test loadVersionInfo function
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\ContentHandler::loadVersionInfo
     * @group contentHandler
     */
    public function testLoadVersionInfo()
    {
        $contentHandler = $this->persistenceHandler->contentHandler();

        $versionInfo = $contentHandler->loadVersionInfo( 1, 2 );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo', $versionInfo );

        $this->assertEquals( 2, $versionInfo->id );
        $this->assertEquals( 2, $versionInfo->versionNo );
        $this->assertEquals( 1, $versionInfo->contentInfo->id );
    }

    /**
     * Test deleteVersion function
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\ContentHandler::deleteVersion
     * @covers \eZ\Publish\Core\Persistence\InMemory\ContentHandler::loadVersionInfo
     * @group contentHandler
     */
    public function testDeleteVersion()
    {
        $contentHandler = $this->persistenceHandler->contentHandler();
        $contentHandler->deleteVersion( 1, 2 );

        try
        {
            $versionInfo = $contentHandler->loadVersionInfo( 1, 2 );
            $this->fail( "Version not removed correctly" );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test loadDraftsForUser function
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\ContentHandler::loadDraftsForUser
     * @group contentHandler
     */
    public function testLoadDraftsForUser()
    {
        $contentHandler = $this->persistenceHandler->contentHandler();

        $versionInfos = $contentHandler->loadDraftsForUser( 14 );

        $this->assertEquals(
            2,
            count( $versionInfos )
        );

        $this->assertEquals(
            2,
            $versionInfos[0]->id
        );
        $this->assertEquals(
            0,
            $versionInfos[0]->status
        );
        $this->assertEquals(
            $this->content->versionInfo->id,
            $versionInfos[1]->id
        );
        $this->assertEquals(
            0,
            $versionInfos[1]->status
        );
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
        $this->assertEquals( 1, $copy->versionInfo->contentInfo->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->versionInfo->contentInfo->contentTypeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->versionInfo->contentInfo->ownerId, "Owner ID does not match" );
        $this->assertEquals( 1, $copy->versionInfo->contentInfo->currentVersionNo, "Current version no does not match" );
        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent(
            $copy->versionInfo->contentInfo->id
        );
        $this->assertEmpty( $locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $copy->versionInfo->contentInfo->id );
        $this->assertEquals( 1, count( $versions ) );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $versions[0]->names );
        $this->assertEquals( 1, $versions[0]->versionNo, "Version number does not match" );
        $this->assertEquals( 14, $versions[0]->creatorId, "Creator ID does not match" );
        $this->assertEquals( $copy->versionInfo->contentInfo->id, $versions[0]->contentInfo->id );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->modificationDate );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->creationDate );
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
        $this->assertEquals( 1, $copy->versionInfo->contentInfo->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->versionInfo->contentInfo->contentTypeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->versionInfo->contentInfo->ownerId, "Owner ID does not match" );
        $this->assertEquals( $versionNoToCopy, $copy->versionInfo->contentInfo->currentVersionNo, "Current version no does not match" );
        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent(
            $copy->versionInfo->contentInfo->id
        );
        $this->assertEmpty( $locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $copy->versionInfo->contentInfo->id );
        $this->assertEquals( 1, count( $versions ) );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $versions[0]->names );
        $this->assertEquals( 2, $versions[0]->versionNo, "Version number does not match" );
        $this->assertEquals( 14, $versions[0]->creatorId, "Creator ID does not match" );
        $this->assertEquals( $copy->versionInfo->contentInfo->id, $versions[0]->contentInfo->id );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->modificationDate );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->creationDate );
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
        $copy = $contentHandler->copy( 1 );
        $this->assertEquals( 1, $copy->versionInfo->contentInfo->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->versionInfo->contentInfo->contentTypeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->versionInfo->contentInfo->ownerId, "Owner ID does not match" );
        $this->assertEquals( 1, $copy->versionInfo->contentInfo->currentVersionNo, "Current version no does not match" );
        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent(
            $copy->versionInfo->contentInfo->id
        );
        $this->assertEmpty( $locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $copy->versionInfo->contentInfo->id );
        $this->assertEquals( 2, count( $versions ) );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $versions[0]->names );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $versions[1]->names );
        $this->assertEquals( 1, $versions[0]->versionNo );
        $this->assertEquals( 2, $versions[1]->versionNo );
        $this->assertEquals( 14, $versions[0]->creatorId );
        $this->assertEquals( 14, $versions[1]->creatorId );
        $this->assertEquals( $copy->versionInfo->contentInfo->id, $versions[0]->contentInfo->id );
        $this->assertEquals( $copy->versionInfo->contentInfo->id, $versions[1]->contentInfo->id );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->modificationDate );
        $this->assertGreaterThanOrEqual( $time, $versions[1]->modificationDate );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->creationDate );
        $this->assertGreaterThanOrEqual( $time, $versions[1]->creationDate );
    }

    /**
     * Test updateContent function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::updateContent
     * @group contentHandler
     */
    public function testUpdateContent()
    {
        $time = time();
        $struct = new UpdateStruct;
        $struct->name = array( "eng-GB" => "All shiny new name" );
        $struct->creatorId = 10;
        $struct->modificationDate = $time;
        $struct->initialLanguageId = 2;
        $struct->fields[] = new Field(
            array(
                "id" => $this->content->fields[0]->id,
                "fieldDefinitionId" => $this->content->fields[0]->fieldDefinitionId,
                "value" => new FieldValue(
                    array(
                        "data" => "Welcome back"
                    )
                ),
                "languageCode" => "eng-GB"
            )
        );

        $content = $this->persistenceHandler->contentHandler()->updateContent( $this->contentId, 1, $struct );

        $this->assertTrue( $content instanceof Content );
        $this->assertEquals( $this->contentId, $content->versionInfo->contentInfo->id );
        $this->assertEquals( VersionInfo::STATUS_DRAFT, $content->versionInfo->status );
        $this->assertEquals( 10, $content->versionInfo->creatorId );
        $this->assertEquals( $time, $content->versionInfo->modificationDate );
        $this->assertEquals( array( "eng-GB" => "All shiny new name" ), $content->versionInfo->names );
        $this->assertEquals(
            $this->persistenceHandler->contentLanguageHandler()->load( $struct->initialLanguageId )->languageCode,
            $content->versionInfo->initialLanguageCode
        );

        $this->assertEquals(
            reset( $struct->fields )->value->data,
            reset( $content->fields )->value->data
        );
    }

    /**
     * Tests createDraftFromVersion()
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

        $draft = $contentHandler->createDraftFromVersion( $content->versionInfo->contentInfo->id, 1, 10 );

        self::assertSame( $content->versionInfo->contentInfo->currentVersionNo + 1, $draft->versionInfo->versionNo );
        self::assertGreaterThanOrEqual( $time, $draft->versionInfo->creationDate );
        self::assertGreaterThanOrEqual( $time, $draft->versionInfo->modificationDate );
        self::assertSame( VersionInfo::STATUS_DRAFT, $draft->versionInfo->status, 'Created version must be a draft' );
        self::assertSame( $content->versionInfo->contentInfo->id, $draft->versionInfo->contentInfo->id );
        self::assertSame( $content->versionInfo->initialLanguageCode, $draft->versionInfo->initialLanguageCode );
        self::assertSame( $content->versionInfo->languageIds, $draft->versionInfo->languageIds );

        // Indexing fields by definition id to be able to compare them
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
        $this->persistenceHandler->contentHandler()->setStatus( $content->versionInfo->contentInfo->id, VersionInfo::STATUS_PUBLISHED, $content->versionInfo->versionNo );
        $content = $this->persistenceHandler->contentHandler()->load( $content->versionInfo->contentInfo->id, $content->versionInfo->versionNo );

        self::assertEquals( VersionInfo::STATUS_PUBLISHED, $content->versionInfo->status );
    }
}
